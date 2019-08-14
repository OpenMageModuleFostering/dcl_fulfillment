<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_ApiController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $parms = $this->getRequest()->getParams();
        $is_authenticated = false;
        $api = "";

        if (isset($parms['api'])) $api = $parms['api'];

        $store_id = -1;
        try {
            $store_id = $parms['store_id'];
        }
        catch (Exception $e) { } //
        try {
            $auth = $this->getRequest()->getHeader('X-EFACTORY-AUTH');
            if ($store_id > 0) {
                $base64 = base64_encode(Mage::getStoreConfig('dcl_fulfillment/general/api_username', $store_id) . ':' . 
                                        Mage::getStoreConfig('dcl_fulfillment/general/api_key', $store_id));
                $is_authenticated = strcasecmp($base64, $auth) == 0;
            }
        }
        catch (Exception $e) { } //

        // ====================================================================================
        if ($is_authenticated && $api == "settings") {
            $allStores = Mage::app()->getStores();
            $config = null;
            foreach ($allStores as $_eachStoreId => $val) 
            {
                $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                if ($store_id != $_storeId) continue;
                $configStore = Mage::getStoreConfig('dcl_fulfillment', $_storeId);
                $configStore['general']['store_id'] = $_storeId;
                $map_table = unserialize($configStore['shipping']['shippingtable']);
                $configStore['shipping_methods'] = [];
                unset($configStore['general']['api_username']);
                unset($configStore['general']['api_password']);
                unset($configStore['general']['efactory_username']);
                unset($configStore['general']['efactory_password']);

                foreach ($map_table as $key => $value) {
                    if (!empty($value['dcl_carrier'])) {
                        $configStore['shipping_methods'][] = $value;
                    }
                }
                unset($configStore['shipping']);
                $config = $configStore;
            }
            header('Content-Type: application/json', true);
            ob_end_clean();
            $response['settings'] = $config;
            echo json_encode($response);
        }
        // ====================================================================================
        else if ($is_authenticated && $api == "open_orders") {
            $orders = [];

            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) 
            {
                $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                if ($store_id != $_storeId) continue;

                $configStore = Mage::getStoreConfig('dcl_fulfillment', $_storeId);

                // Check for store enabled condition ------------------------------
                if ($configStore['general']['enabled'] == '0') continue;
                // ----------------------------------------------------------------

                // Check for day import condition ---------------------------------
                $datetime_at_dcl = new DateTime("now", new DateTimeZone('America/Los_Angeles') );
                $dw = date("w", $datetime_at_dcl->format('U')); // 0 = Sunday in Los Angeles time
                if (!in_array($dw, explode(',',$configStore['general']['importdays']))) continue;
                // ----------------------------------------------------------------

                // Check for time import condition --------------------------------
                $from_time_array = explode(',',$configStore['general']['importtimefrom']);
                $from_time = $from_time_array[2] + $from_time_array[1] * 60 + $from_time_array[0] * 60 * 60;

                $to_time_array = explode(',',$configStore['general']['importtimeto']);
                $to_time = $to_time_array[2] + $to_time_array[1] * 60 + $to_time_array[0]*60*60;

                $import_time_array = explode(',',$datetime_at_dcl->format('H,i,s'));
                $import_time = $import_time_array[2] + $import_time_array[1] * 60 + $import_time_array[0] * 60 * 60;
                if (!($import_time >= $from_time && $import_time <= $to_time)) continue;
                // ----------------------------------------------------------------

                $collection = Mage::getModel("sales/order")->getCollection()

                // Check for status condition -------------------------------------
                ->addFieldToFilter('status',
                    array(
                        'in' => explode(',',$configStore['general']['processstatus'])
                ))
                // ----------------------------------------------------------------

                ->addAttributeToFilter('store_id', $_storeId);
                // ----------------------------------------------------------------

                $now_gmt = Mage::app()->getLocale()->utcDate(null, Mage::getModel('core/date')->date('Y-m-d H:i:s'), true, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

                // Skip for order already acknowledged by DCL-------------------
                $collection->getSelect()->joinLeft( array('ack_order'=> 'dcl_fulfillment_acknowledgedorder'), 'ack_order.order_id = main_table.entity_id', null)
                ->where("ack_order.order_id is null");
                // ------------------------------------------------------------

                foreach ($collection as $col) {
                    $order = new StdClass;
                    $order->order_id = $col->getId();
                    $order->incremental_id = $col->getIncrementId();
                    $order->store_id = $col->getStoreId();
                    $order->store_name = $col->getStoreName();
                    $order->status = $col->getStatus();
                    $order->created_at = $col->getCreatedAt(); // GMT date/time

                    // Check for order age condition ------------------------------
                    $start_date = new DateTime($now_gmt);
                    $since_start = $start_date->diff(new DateTime($col->getCreatedAt()));
                    $minutes = $since_start->days * 24 * 60;
                    $minutes += $since_start->h * 60;
                    $minutes += $since_start->i;
                    if ($minutes < (int)$configStore['general']['ordertime']) continue;
                    // ------------------------------------------------------------

                    $order->updated_at = $col->getUpdatedAt();
                    $order->customer_id = $col->getCustomerId();
                    
                    $order->tax_amount = $col->getTaxAmount();
                    $order->shipping_amount = $col->getShippingAmount();
                    $order->discount_amount = $col->getDiscountAmount();
                    $order->subtotal = $col->getSubtotal();
                    $order->grand_total = $col->getGrandTotal();
                    $order->shipping_method = $col->getShippingMethod();
                    $order->shipping_description = $col->getShippingDescription();

                    $_shippingAddress = $col->getShippingAddress();
                    $order->shipping_address = new StdClass;
                    $order->shipping_address->name = trim($_shippingAddress->getFirstname()." ".$_shippingAddress->getLastname());
                    $order->shipping_address->company = $_shippingAddress->getCompany();
                    $order->shipping_address->email = $_shippingAddress->getEmail();
                    $order->shipping_address->phone = $_shippingAddress->getTelephone();
                    $order->shipping_address->address1 = $_shippingAddress->getStreet1();
                    $order->shipping_address->address2 = $_shippingAddress->getStreet2();
                    $order->shipping_address->city = $_shippingAddress->getCity();
                    $order->shipping_address->state = $_shippingAddress->getRegion();
                    $order->shipping_address->postal_code = $_shippingAddress->getPostcode();
                    $order->shipping_address->country_code = $_shippingAddress->getCountry_id();
                    
                    $_billingAddress = $col->getBillingAddress();
                    $order->billing_address = new StdClass;
                    $order->billing_address->name = trim($_billingAddress->getFirstname()." ".$_billingAddress->getLastname());
                    $order->billing_address->company = $_billingAddress->getCompany();
                    $order->billing_address->email = $_billingAddress->getEmail();
                    $order->billing_address->phone = $_billingAddress->getTelephone();
                    $order->billing_address->address1 = $_billingAddress->getStreet1();
                    $order->billing_address->address2 = $_billingAddress->getStreet2();
                    $order->billing_address->city = $_billingAddress->getCity();
                    $order->billing_address->state = $_billingAddress->getRegion();
                    $order->billing_address->postal_code = $_billingAddress->getPostcode();
                    $order->billing_address->country_code = $_billingAddress->getCountry_id();

                    // Just in case someone add custom attributes to order
                    $ord = Mage::getModel('sales/order')->load($col->getId());
                    $order->shipping_instructions =  $ord->getData('shipping_instructions');
                    $order->extra_field1 =  $ord->getData('extra_field1');
                    $order->extra_field2 =  $ord->getData('extra_field2');
                    $order->extra_field3 =  $ord->getData('extra_field3');
                    $order->extra_field4 =  $ord->getData('extra_field4');
                    $order->extra_field5 =  $ord->getData('extra_field5');

                    $products = array();
                    $items = $col->getAllVisibleItems();
                    foreach($items as $i) {
                        $product_it['item_id'] =  $i->getId();
                        $product_it['sku'] =  $i->getSku();
                        $product_it['name'] =  $i->getName();
                        $product_it['qty'] =  (int)$i->getQtyOrdered();
                        $product_it['price'] =  $i->getPrice();
    
                        // Just in case someone add custom attributes to product
                        $product = Mage::getModel('catalog/product')->load($i->getProductId());
                        $product_it['extra_field1'] =  $product->getData('extra_field1'); // same as $product->getExtraField1();
                        $product_it['extra_field2'] =  $product->getData('extra_field2');
                        $product_it['extra_field3'] =  $product->getData('extra_field3');
                        $product_it['extra_field4'] =  $product->getData('extra_field4');
                        $product_it['extra_field5'] =  $product->getData('extra_field5');
                        
                        $products[] = $product_it;
                    }
                       $order->items = $products;

                    $orders[] = $order;
                }
            }
            header('Content-Type: application/json', true);
            ob_end_clean();
            $responde['orders'] = $orders;
            echo json_encode($responde);
        }
        // ====================================================================================
        else if ($is_authenticated && $api == "acknowledged_orders") {
            // Example: data={"acknowledge_only": true, "orders": [3]}
            $acknowledged_orders = [];
            $post_orders = $this->getRequest()->getPost('data', null);
            if (!empty($post_orders)) {
                $response = json_decode($post_orders);
                $order_ids = $response->orders;
                foreach ($order_ids as $order_id) {
                    // Insert order_id into DCL acklowledge table
                    $model = Mage::getModel('dcl_fulfillment/acknowledgedorder');
                    $model->setData(array('order_id' => (int)$order_id, 'stage' => 0, 'stage_description' => ($response->acknowledge_only? 'Acknowledged Only': 'Acknowledged')));
                    try {
                        $model->save();
                        $order = Mage::getModel('sales/order')->load((int)$order_id);
                        if ($order) {
                            if ($store_id != $order->getStoreId()) continue;
                            // Add the comment and save the order
                            $order->addStatusHistoryComment('[DCL] Order transferred to DCL.', false);

                            // Change order status (if needed)
                            $configStore = Mage::getStoreConfig('dcl_fulfillment', $order->getStoreId());
                            $ack_status = $configStore['general']['acknowledgetatus'];
                            if (!empty($ack_status)) {
                                $order->setStatus($ack_status);
                            }

                            $order->save();
                        }
                        $acknowledged_orders[] = (int)$order_id;
                    }
                    catch (Exception $e) { } //
                }
            }
            $obj = (object) array('acknowledged_orders' => $acknowledged_orders);
            header('Content-Type: application/json', true);
            ob_end_clean();
            $responde['result'] = $obj;
            echo json_encode($responde);
        }
        // ====================================================================================
        else if ($is_authenticated && $api == "order_stages") {
            // Example: data=[{"order_id": 5, "stage": 10, "stage_description": "Received"}, ...]
            $order_updated = 0;
            $post= $this->getRequest()->getPost('data', null);
            if (!empty($post)) {
                $order_list = json_decode($post);
                foreach ($order_list as $order_obj) {
                    $order_id = (int)$order_obj->order_id;
                    $order = Mage::getModel('sales/order')->load($order_id);
                    if ($order) {
                        if ($store_id != $order->getStoreId()) continue;
                        // Add the comment and save the order
                        $order->addStatusHistoryComment('[DCL] Order stage: ' . $order_obj->stage_description .'.', false);
                        try {
                            $order->save();
                            $order_updated++;

                            $model = Mage::getModel('dcl_fulfillment/acknowledgedorder')
                            ->getCollection()
                            ->addFieldToFilter('order_id', array('eq' => $order_id))
                            ->getFirstItem();
                            if ($model) {
                                try {
                                    $model->addData(array('order_id' => $order_id, 'stage' => $order_obj->stage, 'stage_description' => $order_obj->stage_description));
                                    $model->save();
                                }
                                catch (Exception $e) { } //        
                            }
                        }
                        catch (Exception $e) { } //
                    }
                }
            }
            $obj = (object) array('order_updated' => $order_updated);
            header('Content-Type: application/json', true);
            ob_end_clean();
            $responde['result'] = $obj;
            echo json_encode($responde);
        }
        // ====================================================================================
        else if ($is_authenticated && $api == "order_fulfillments") {
            // Example: data=[{"order_id": 3, "carrier": "UPS", "service": "GROUND", "tracking_number": "1Z921321764238", "items": [{"sku": "3", "qty": 1}] }, ...]

            $fulfillments = 0;
            $post = $this->getRequest()->getPost('data', null);
            if (!empty($post)) {
                $order_list = json_decode($post);
                foreach ($order_list as $order_obj) {
                    $order_id = $order_obj->order_id;

                    $order = Mage::getModel('sales/order')->load($order_id);
                    if ($order && $order->canShip()) {
                        if ($store_id != $order->getStoreId()) continue;
                        $shipped_items = array();
                        $items[] = $order_obj->items;
                        foreach ($order_obj->items as $item) {
                            $shipped_items[$item->sku] = $item->qty;
                        }

                        $configStore = Mage::getStoreConfig('dcl_fulfillment', $order->getStoreId());
                        $notify = $configStore['general']['notification'];
                        try {
                            $shipment = $order->prepareShipment($shipped_items);
                            if ($shipment) {
                                $shipment->register();
                                if ($notify == '1') {
                                    $shipment->setEmailSent(true);
                                }
                                $shipment->getOrder()->setIsInProcess(true);
                                try {
                                    $transactionSave = Mage::getModel('core/resource_transaction')
                                        ->addObject($shipment)
                                        ->addObject($shipment->getOrder())
                                        ->save();
                                    $shipment->sendEmail($notify == '1', '');
                                } catch (Mage_Core_Exception $e) {
                                    //
                                }
                                $shipmentId = $shipment->getIncrementId();
                                $carrier = '';
                                switch (strtolower($order_obj->carrier))
                                {
                                    // Standard Magento carriers
                                    case "ups":
                                    case "usps":
                                    case "fedex":
                                    case "dhl":
                                        $carrier = strtolower($order_obj->carrier);
                                    break;
                                default:
                                    $carrier = "custom";
                                    break;
                                }

                                Mage::getModel('sales/order_shipment_api')
                                ->addTrack($shipmentId, $carrier, $order_obj->service, $order_obj->tracking_number);
                                
                                $fulfillments++;
                            }
                        }
                        catch (Exception $e) { } //
                    }
                }
            }
            $obj = (object) array('order_fulfilled' => $fulfillments);
            header('Content-Type: application/json', true);
            ob_end_clean();
            echo json_encode($obj);
        }
        // ====================================================================================
        else if ($is_authenticated && $api == "inventory") {
            // Store independent
            // Example: data={"1002-002-DEMO2": 10, "1003-003-DEMO3": 5, ...}
            $obj = null;
            $post = $this->getRequest()->getPost('data', null);
            if (!empty($post)) {
                // POST
                $sku_updated = 0;
                $configStore = Mage::getStoreConfig('dcl_fulfillment'); // Common to all stores
                if ($configStore['general']['inventory'] == '1') {
                    $sku_qty = json_decode($post);
                    foreach ($sku_qty as $sku => $qty) {
                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                        if ($product) {
                            $productId = $product->getId();
                            $stockItem =Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);

                            if ($configStore['general']['instock'] == '1' && (int)$qty > 0) {
                                $stockItem->setData('is_in_stock', 1);
                            }
                            $stockItem->setData('qty', (int)$qty);

                            try {
                                $stockItem->save();
                                $sku_updated++;

                                // Add/update DCL inventory table
                                $model = Mage::getModel('dcl_fulfillment/inventory')
                                ->getCollection()
                                ->addFieldToFilter('sku', array('eq' => $sku))
                                ->getFirstItem();
                                if ($model) {
                                    try {
                                        $model->addData(array('sku' => $sku, 'qty' => (int)$qty));
                                        $model->save();
                                    }
                                    catch (Exception $e) { } //        
                                }
                                else {
                                    $model = Mage::getModel('dcl_fulfillment/inventory');
                                    try {
                                        $model->setData(array('sku' => $sku, 'qty' => (int)$qty));
                                        $model->save();
                                    }
                                    catch (Exception $e) { } //        
                                }
                            }
                            catch (Exception $e) { } //
                        }
                    }
                }
                $obj = (object) array('sku_updated' => $sku_updated);
                $responde['result'] = $obj;
            }
            else {
                // GET
                $products = [];
                $productCollection = Mage::getModel('catalog/product')->getCollection();
                foreach ($productCollection as $product) {
                    $productId = $product->getId();
                    $stockItem =Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                    $products[] = array('sku' => $product->sku, 'qty' => (int)$stockItem->getData('qty'));
                }
                $responde['result'] = $products;
            }
            header('Content-Type: application/json', true);
            ob_end_clean();
            echo json_encode($responde);
        }
        // ====================================================================================
        else { // Do not return any clue about authorization required! Return info for troubleshooting.
            $obj = (object) array('plugin_name' => 'dcl_fulfillment',
                                  'plugin_version' => (string)Mage::getConfig()->getModuleConfig('DCL_Fulfillment')->version,
                                  'is_magento_enterprise' => Mage::helper('dcl_fulfillment/admin')->isMageEnterprise(),
                                  'is_magento_professional' => Mage::helper('dcl_fulfillment/admin')->isMageProfessional(),
                                  'is_magento_community' => Mage::helper('dcl_fulfillment/admin')->isMageCommunity(),
                                  'magento_version' => Mage::getVersion(),
                                  'php_version' => phpversion(),
                                  'os' => php_uname(),
                                  );
            header('Content-Type: application/json', true);
            ob_end_clean();
            echo json_encode($obj);
        }
    }
}

?>
