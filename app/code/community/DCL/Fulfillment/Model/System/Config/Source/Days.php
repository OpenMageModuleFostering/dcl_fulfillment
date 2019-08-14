<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_Model_System_Config_Source_Days
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Monday')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Tuesday ')),
            array('value' => 3, 'label'=>Mage::helper('adminhtml')->__('Wednesday ')),
            array('value' => 4, 'label'=>Mage::helper('adminhtml')->__('Thursday ')),
            array('value' => 5, 'label'=>Mage::helper('adminhtml')->__('Friday ')),
            array('value' => 6, 'label'=>Mage::helper('adminhtml')->__('Saturday ')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Sunday ')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            1 => Mage::helper('adminhtml')->__('Monday'),
            2 => Mage::helper('adminhtml')->__('Tuesday'),
            3 => Mage::helper('adminhtml')->__('Wednesday'),
            4 => Mage::helper('adminhtml')->__('Thursday'),
            5 => Mage::helper('adminhtml')->__('Friday'),
            6 => Mage::helper('adminhtml')->__('Saturday'),
            0 => Mage::helper('adminhtml')->__('Sunday'),
        );
    }
}

?>