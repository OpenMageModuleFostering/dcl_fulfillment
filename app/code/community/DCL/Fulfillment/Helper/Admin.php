<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_Helper_Admin extends Mage_Core_Helper_Abstract
{
    public function isAccountSetup() {
        $code = Mage::getSingleton('adminhtml/config_data')->getStore();
        $store_id = Mage::getModel('core/store')->load($code)->getId();
        $account = Mage::getStoreConfig('dcl_fulfillment/general/account_number', $store_id);
        return $account != '';
    }

    public function setDefaultShippingData() {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shipping = array();

        foreach($methods as $_ccode => $_carrier)
        {
            if ($_methods = $_carrier->getAllowedMethods())
            {
                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title")) $_title = $_ccode;
                foreach ($_methods as $_mcode => $_method)
                {
                    $dcl_carrier_service = $this->getDefaultDCLCarrier($_ccode .'_'. $_mcode);
                    $shipping[$_ccode .'_'. $_mcode] = array (
                        'ship_name' => $_title . ' - ' . $_method,
                        'ship_code' => $_ccode .'_'. $_mcode,
                        'dcl_carrier' => $dcl_carrier_service[0],
                        'dcl_service' => $dcl_carrier_service[1],
                    );
                }
            }
        }

        // sort before saving?
        $installer = new Mage_Eav_Model_Entity_Setup('core_setup');
        $installer->startSetup();
        
        // Load full default settings
        // It keeps track also of the shipping method settings that are not currently active
        $storedArray = unserialize(Mage::getStoreConfig('dcl_fulfillment/shipping/shippingtable_full'));
        if (is_array($storedArray)) {
            foreach ($shipping as $key => $value) {
                // Add missing (now active)
                if (!array_key_exists ($key, $storedArray)) {
                    $storedArray[$key] = $value;
                }
            }
        }
        else $storedArray = $shipping;

        // Merge with values just saved (if any)
        $savedArray = unserialize(Mage::getStoreConfig('dcl_fulfillment/shipping/shippingtable'));
        if (is_array($savedArray)) {
            foreach ($savedArray as $key => $value) {
                // Add missing (now active)
                if (array_key_exists ($key, $storedArray)) {
                    $storedArray[$key] = $value;
                }
            }
        }

        // Save full updated default config
        $installer->setConfigData("dcl_fulfillment/shipping/shippingtable_full", serialize($storedArray));

        // remove no more active shipping method
        foreach ($storedArray as $key => $value) {
            if (!array_key_exists ($key, $shipping)) {
                unset($storedArray[$key]);
            }
            else {
                // Update ship name
                $storedArray[$key]['ship_name'] = $shipping[$key]['ship_name'];
            }
        }
        $installer->setConfigData("dcl_fulfillment/shipping/shippingtable", serialize($storedArray));
        
        $installer->endSetup();
    }
    
    private function getDefaultDCLCarrier($ship_code) {
        $array = ['',''];

        switch($ship_code) {
            // USPS default mapping
            case 'usps_0_FCLE':
                $array[0] = 'USPS'; $array[1] = 'FIRST CLASS'; break;
            case 'usps_0_FCL':
                $array[0] = 'USPS'; $array[1] = 'FIRST CLASS'; break;
            case 'usps_0_FCP':
                $array[0] = 'USPS'; $array[1] = 'FIRST CLASS'; break;
            case 'usps_1':
                $array[0] = 'USPS'; $array[1] = 'PRIORITY MAIL'; break;
            case 'usps_3':
                $array[0] = 'USPS'; $array[1] = 'EXPRESS MAIL INTL ENDICIA'; break;
            case 'usps_4':
                $array[0] = 'USPS'; $array[1] = 'PARCEL POST ENDICIA'; break;
            case 'usps_16':
                $array[0] = 'USPS'; $array[1] = 'Priority Mail FlatRateBox-ENV'; break;
            case 'usps_17':
                $array[0] = 'USPS'; $array[1] = 'Priority Mail FlatRateBox-M'; break;
            case 'usps_22':
                $array[0] = 'USPS'; $array[1] = 'Priority Mail FlatRateBox-L'; break;
            case 'usps_28':
                $array[0] = 'USPS'; $array[1] = 'Priority Mail FlatRateBox-S'; break;
            case 'usps_42':
                $array[0] = 'USPS'; $array[1] = 'Priority Mail FlatRateBox-ENV'; break;
            case 'usps_61':
                $array[0] = 'USPS'; $array[1] = 'FIRST CLASS'; break;
            case 'usps_INT_1':
                $array[0] = 'USPS'; $array[1] = 'EXPRESS MAIL INTL ENDICIA'; break;
            case 'usps_INT_2':
                $array[0] = 'USPS'; $array[1] = 'PRIORITY MAIL INTL ENDICIA'; break;
            case 'usps_INT_14':
                $array[0] = 'USPS'; $array[1] = 'FIRST CLASS'; break;
            case 'usps_INT_15':
                $array[0] = 'USPS'; $array[1] = 'First Class Mail International'; break;
            
            // FEDEX default mapping
            case 'fedex_FEDEX_1_DAY_FREIGHT':
                $array[0] = 'FEDEX'; $array[1] = 'OVERNIGHT FREIGHT'; break;
            case 'fedex_FEDEX_2_DAY_FREIGHT':
                $array[0] = 'FEDEX'; $array[1] = '2DAY FREIGHT'; break;
            case 'fedex_FEDEX_2_DAY':
                $array[0] = 'FEDEX'; $array[1] = '2DAY'; break;
            case 'fedex_FEDEX_EXPRESS_SAVER':
                $array[0] = 'FEDEX'; $array[1] = 'EXPRESS SAVER'; break;
            case 'fedex_FEDEX_GROUND':
                $array[0] = 'FEDEX'; $array[1] = 'GROUND'; break;
            case 'fedex_FIRST_OVERNIGHT':
                $array[0] = 'FEDEX'; $array[1] = 'First Overnight'; break;
            case 'fedex_GROUND_HOME_DELIVERY':
                $array[0] = 'FEDEX'; $array[1] = 'HOME DELIVERY'; break;
            case 'fedex_INTERNATIONAL_ECONOMY':
                $array[0] = 'FEDEX'; $array[1] = 'INTERNATIONAL ECONOMY'; break;
            case 'fedex_INTERNATIONAL_ECONOMY_FREIGHT':
                $array[0] = 'FEDEX'; $array[1] = 'INTL ECONOMY FREIGHT'; break;
            case 'fedex_INTERNATIONAL_PRIORITY':
                $array[0] = 'FEDEX'; $array[1] = 'INTERNATIONAL PRIORITY'; break;
            case 'fedex_INTERNATIONAL_PRIORITY_FREIGHT':
                $array[0] = 'FEDEX'; $array[1] = 'INTL PRIORITY FREIGHT'; break;
            case 'fedex_PRIORITY_OVERNIGHT':
                $array[0] = 'FEDEX'; $array[1] = 'PRIORITY OVERNIGHT'; break;
            case 'fedex_STANDARD_OVERNIGHT':
                $array[0] = 'FEDEX'; $array[1] = 'STANDARD OVERNIGHT'; break;
            case 'fedex_FEDEX_FREIGHT':
                $array[0] = 'FEDEX'; $array[1] = 'FREIGHT'; break;
            
            // UPS default mapping
            case 'ups_1DM':
                $array[0] = 'UPS'; $array[1] = 'NEXT DAY AIR EARLY AM'; break;
            case 'ups_1DA':
                $array[0] = 'UPS'; $array[1] = 'NEXT DAY AIR'; break;
            case 'ups_1DP':
                $array[0] = 'UPS'; $array[1] = 'NEXT DAY AIR SAVER'; break;
            case 'ups_2DM':
                $array[0] = 'UPS'; $array[1] = '2ND DAY AIR AM'; break;
            case 'ups_2DA':
                $array[0] = 'UPS'; $array[1] = '2ND DAY AIR'; break;
            case 'ups_3DS':
                $array[0] = 'UPS'; $array[1] = '3 DAY SELECT'; break;
            case 'ups_GND':
                $array[0] = 'UPS'; $array[1] = 'GROUND'; break;
            case 'ups_GNDCOM':
                $array[0] = 'UPS'; $array[1] = 'GROUND SERVICE COMMERCIAL'; break;
            case 'ups_GNDRES':
                $array[0] = 'UPS'; $array[1] = 'GROUND SERVICE RESIDENTIAL'; break;
            case 'ups_STD':
                $array[0] = 'UPS'; $array[1] = 'STANDARD TO CANADA'; break;
            case 'ups_XPR':
                $array[0] = 'UPS'; $array[1] = 'WORLDWIDE EXPRESS'; break;
            case 'ups_WXS':
                $array[0] = 'UPS'; $array[1] = 'WORLDWIDE EXPRESS SAVER'; break;
            case 'ups_XPD':
                $array[0] = 'UPS'; $array[1] = 'WORLDWIDE EXPEDITED'; break;
        }

        return $array;
    }

    public function generateAPICredentialsForStore($store_id) {
       // Load full default settings
        // It keeps track also of the shipping method settings that are not currently active
        $APIUsername = Mage::getStoreConfig('dcl_fulfillment/general/api_username', $store_id);
        if (empty($APIUsername)) {
            $installer = new Mage_Eav_Model_Entity_Setup('core_setup');
            $installer->startSetup();

            $username = 'dcl_' . $this->generateRandomString(10);
            $password = $this->generateRandomString(24);
            $installer->setConfigData("dcl_fulfillment/general/api_username", $username, 'stores', $store_id);
            $installer->setConfigData("dcl_fulfillment/general/api_key", $password, 'stores', $store_id);

            $installer->endSetup();
            return array($username, $password);
        }
        else {
            return array(Mage::getStoreConfig('dcl_fulfillment/general/api_username', $store_id),
                         Mage::getStoreConfig('dcl_fulfillment/general/api_key', $store_id)
                        );
        }
    }

    private function generateRandomString($length = 10) {
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        srand($this->make_seed());
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function make_seed()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }

    public function isMageEnterprise() {
        return Mage::getConfig ()->getModuleConfig ( 'Enterprise_Enterprise' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_AdminGws' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Checkout' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Customer' );
    }

    public function isMageProfessional() {
        return Mage::getConfig ()->getModuleConfig ( 'Enterprise_Enterprise' ) && !Mage::getConfig ()->getModuleConfig ( 'Enterprise_AdminGws' ) && !Mage::getConfig ()->getModuleConfig ( 'Enterprise_Checkout' ) && !Mage::getConfig ()->getModuleConfig ( 'Enterprise_Customer' );
    }

    public function isMageCommunity() {
        return !$this->isMageEnterprise() && !$this->isMageProfessional();
    }
}

?>