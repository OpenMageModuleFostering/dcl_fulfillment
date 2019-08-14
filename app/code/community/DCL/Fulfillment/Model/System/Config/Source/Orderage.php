<?php

/**
 * @author DCL
 */

class DCL_Fulfillment_Model_System_Config_Source_Orderage
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('No wait')),
            array('value' => 30, 'label'=>Mage::helper('adminhtml')->__('30 min')),
            array('value' => 60, 'label'=>Mage::helper('adminhtml')->__('1 hour')),
            array('value' => 120, 'label'=>Mage::helper('adminhtml')->__('2 hours')),
            array('value' => 240, 'label'=>Mage::helper('adminhtml')->__('4 hours')),
            array('value' => 360, 'label'=>Mage::helper('adminhtml')->__('6 hours')),
            array('value' => 720, 'label'=>Mage::helper('adminhtml')->__('12 hours')),
            array('value' => 1440, 'label'=>Mage::helper('adminhtml')->__('1 day')),
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
            0 => Mage::helper('adminhtml')->__('No wait'),
            30 => Mage::helper('adminhtml')->__('30 min'),
            60 => Mage::helper('adminhtml')->__('1 hour'),
            120 => Mage::helper('adminhtml')->__('2 hours'),
            240 => Mage::helper('adminhtml')->__('4 hours'),
            360 => Mage::helper('adminhtml')->__('6 hours'),
            720 => Mage::helper('adminhtml')->__('12 hours'),
            1440 => Mage::helper('adminhtml')->__('1 day'),
        );
    }
}

?>