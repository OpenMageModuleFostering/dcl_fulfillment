<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_Block_Adminhtml_Config_Form_Field_Shippingtable extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct() {
        $this->addColumn('ship_name', array(
            'label' => Mage::helper('dcl_fulfillment')->__('Magento Shipping Method'),
        ));
        $this->addColumn('ship_code', array(
            'label' => Mage::helper('dcl_fulfillment')->__('&nbsp;'),
            'style' => 'display:none',
        ));
        $this->addColumn('dcl_carrier', array(
            'label' => Mage::helper('dcl_fulfillment')->__('DCL Carrier'),
            'style' => 'width:140px',
        ));
        $this->addColumn('dcl_service', array(
            'label' => Mage::helper('dcl_fulfillment')->__('DCL Service'),
            'style' => 'width:250px',
        ));
        $this->_addAfter = false;
        $this->setTemplate('dcl/array.phtml');

        parent::__construct();
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }

        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        if ($columnName == 'ship_name') {
            return '<span style="white-space: nowrap;">#{' . $columnName . '}</span><input type="hidden" name="' . $inputName . '" value="#{' . $columnName . '}" ' . ($column['size'] ? 'size="' . $column['size'] . '"' : '') . '/>';
        }
        else return parent::_renderCellTemplate($columnName);
    }
}

?>