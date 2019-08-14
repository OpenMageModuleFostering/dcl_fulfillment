<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_Model_System_Config_Source_Acknowledgestatus
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $orderStatusCollection = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $status = array();
        $status= array(
            '' => Mage::helper('adminhtml')->__('Select status (if needed)...')
        );
        foreach($orderStatusCollection as $orderStatus) {
            if (!in_array($orderStatus['status'], array('canceled','closed','complete','fraud','paypal_reversed','pending_paypal','paypal_canceled_reversal','payment_review'))) {
                $status[] = array (
                    'value' => $orderStatus['status'], 'label' => $orderStatus['label']
                );
            }
        }        
        return $status;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $orderStatusCollection = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $status = array();
        foreach($orderStatusCollection as $orderStatus) {
            if (!in_array($orderStatus['status'], array('canceled','closed','complete','fraud','paypal_reversed','pending_paypal','paypal_canceled_reversal','payment_review'))) {
                $status[] = array (
                    $orderStatus['status'] => $orderStatus['label']
                );
            }
        }        
        return $status;
    }
}

?>