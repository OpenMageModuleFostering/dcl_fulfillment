<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_Model_Observer
{
    /**
     * Joins extra tables for adding custom columns to Mage_Adminhtml_Block_Sales_Order_Grid
     * @param $observer
     */
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $select->joinLeft(array('ack_order'=> 'dcl_fulfillment_acknowledgedorder'), 'ack_order.order_id = main_table.entity_id', array('stage'=>'stage', 'stage_description' =>'stage_description' ));
    }

    public function onDisplayDCLFulfillmentSection(Varien_Event_Observer $observer)
    {
        if (strpos(Mage::helper('core/url')->getCurrentUrl(), 'section/dcl_fulfillment') !== FALSE ) {
            // Add active shipping methods
            Mage::helper('dcl_fulfillment/admin')->setDefaultShippingData(); 
        }
        return $this;
    }
}

?>