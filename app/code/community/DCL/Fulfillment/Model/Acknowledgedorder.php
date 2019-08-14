<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_Model_Acknowledgedorder extends Mage_Core_Model_Abstract
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        // Called
        $this->_init('dcl_fulfillment/acknowledgedorder');
    }

    /**
     * If object is new adds creation date
     *
     * @return DCL_Fulfillment_Model_Shippingmethod
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->isObjectNew()) {
            $this->setData('acknowledged_at', Varien_Date::now()); // GMT time
        }
        $this->setData('modified_at', Varien_Date::now()); // GMT time
        return $this;
    }
}

?>