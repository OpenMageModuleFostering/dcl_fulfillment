<?php

/**
 * Installation script
 *
 * @author DCL
 */

$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('dcl_fulfillment/acknowledgedorder');
if ($installer->getConnection()->isTableExists($tableName) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('dcl_fulfillment/acknowledgedorder'))
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Entity id')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'identity' => false,
            'nullable' => false,
        ), 'Order id')
        ->addColumn('acknowledged_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => true,
        ), 'Acknowledge Time')
        ->addColumn('modified_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => true,
        ), 'Modified At')
        ->addColumn('stage', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'Stage')
        ->addColumn('stage_description', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
            'nullable' => true,
        ), 'Stage Description')
        ->addIndex($installer->getIdxName(
                $installer->getTable('dcl_fulfillment/acknowledgedorder'),
                array('order_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('order_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        // Speed up order grid (filter by DCL order stage)
        ->addIndex($installer->getIdxName(
                $installer->getTable('dcl_fulfillment/acknowledgedorder'),
                array('stage_description'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('stage_description'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
        )
        ->setComment('DCL Acknowledged Orders');

    $installer->getConnection()->createTable($table);
}

$tableName = $installer->getTable('dcl_fulfillment/inventory');
if ($installer->getConnection()->isTableExists($tableName) != true) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('dcl_fulfillment/inventory'))
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'identity' => true,
            'nullable' => false,
            'primary'  => true,
        ), 'Entity id')
        ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
            'identity' => false,
            'nullable' => false,
        ), 'SKU')
        ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'Qty')
        ->addColumn('modified_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => true,
        ), 'Modified At')
        ->addIndex($installer->getIdxName(
                $installer->getTable('dcl_fulfillment/inventory'),
                array('sku'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('sku'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->setComment('DCL Inventory');

    $installer->getConnection()->createTable($table);
}

$install_version = Mage::getConfig()->getModuleConfig('DCL_Fulfillment')->version;

$msg_title = "DCL Fulfillment {$install_version} was successfully installed! Remember to flush all cache, log-out and log back in.";
$msg_desc = "DCL Fulfillment {$install_version} was successfully installed on your store. "
            . "Remember to flush all cache, log-out and log back in. "
            . "You can configure DCL Fulfillment in the Configuration section.";
$message = Mage::getModel('adminnotification/inbox');
$message->setDateAdded(date("c", time()));

$message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);

$message->setTitle($msg_title);
$message->setDescription($msg_desc);
$message->setUrl("#");
$message->save();

$installer->endSetup(); 

?>
