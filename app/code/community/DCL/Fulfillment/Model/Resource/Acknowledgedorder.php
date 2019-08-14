<?php

/**
 * Acknowledged Order item resource model
 *
 * @author DCL
 */

class DCL_Fulfillment_Model_Resource_Acknowledgedorder extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table and primary key
     */
    protected function _construct()
    {
        // Called
        $this->_init('dcl_fulfillment/acknowledgedorder', 'entity_id');
    }
}

?>