<?php 

/**
 * @author DCL
 */

class DCL_Fulfillment_Block_Adminhtml_Config_Form_Accountnumber extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        $element->setDisabled('disabled');
        $element->setStyle('border: 0; color:#494; background-color:transparent;');
        return parent::_getElementHtml($element);
    }
}

?>