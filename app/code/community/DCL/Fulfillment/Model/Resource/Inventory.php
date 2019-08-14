<?php

/**
 * Inventory item resource model
 *
 * @author DCL
 */

class DCL_Fulfillment_Model_Resource_Inventory extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table and primary key
     */
    protected function _construct()
    {
        $this->_init('dcl_fulfillment/inventory', 'entity_id');
    }
}

?>