<?php

/**
 * Acklowledged Order collection
 *
 * @author DCL
 */

class DCL_Fulfillment_Model_Resource_Acknowledgedorder_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('dcl_fulfillment/acknowledgedorder');
    }
}

?>