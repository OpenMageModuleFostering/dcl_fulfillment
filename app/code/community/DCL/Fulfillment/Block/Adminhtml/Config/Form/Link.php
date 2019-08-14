<?php 

/**
 * @author DCL
 */

class DCL_Fulfillment_Block_Adminhtml_Config_Form_Link extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dcl/link_button.phtml');
    }
 
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
 
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxLinkUrl()
    {
        //return "/dcl_fulfillment/api?api=setup_store";
        return "/dcl_fulfillment/register";
    }
 
    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $code = Mage::getSingleton('adminhtml/config_data')->getStore();
        $store_id = Mage::getModel('core/store')->load($code)->getId();
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'dcl_fulfillment_link',
            'label'     => (Mage::helper('dcl_fulfillment/admin')->isAccountSetup()? $this->helper('adminhtml')->__('Unlink from eFactory Account'): $this->helper('adminhtml')->__('Link to eFactory Account')),
            'onclick'   => 'javascript:linkToEFactory(document.getElementById(\'dcl_fulfillment_general_efactory_username\').value, document.getElementById(\'dcl_fulfillment_general_efactory_password\').value, document.getElementById(\'dcl_fulfillment_general_account_number\').value,\'' . addslashes(Mage::helper('dcl_fulfillment')->__('Link to eFactory Account'))  . '\',\'' . addslashes(Mage::helper('dcl_fulfillment')->__('Unlink from eFactory Account')) . '\',\'' .  addslashes(Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_WEB, true)) . '\', ' . $store_id . '); return false;',
            'style'     => 'margin-bottom: 15px'
        ));
 
        return $button->toHtml();
    }
}

?>