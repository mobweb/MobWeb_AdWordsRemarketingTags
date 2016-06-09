<?php


class MobWeb_AdWordsRemarketingTags_Model_System_Config_Source_ProductId
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'id', 'label' => Mage::helper('adwordsremarketingtags')->__('ID')),
            array('value' => 'sku', 'label' => Mage::helper('adwordsremarketingtags')->__('SKU')),
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
            'id' => Mage::helper('adwordsremarketingtags')->__('ID'),
            'sku' => Mage::helper('adwordsremarketingtags')->__('SKU'),
        );
    }
}
