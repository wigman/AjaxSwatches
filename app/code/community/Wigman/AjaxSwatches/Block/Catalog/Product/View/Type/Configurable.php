<?php
/**
 * Catalog super product configurable part block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 *
 * Wigman changes: We're adding attribute sorting to the mix! See comments below.
 *
 */
class Wigman_AjaxSwatches_Block_Catalog_Product_View_Type_Configurable extends Wigman_AjaxSwatches_Block_Catalog_Product_View_Type_Configurable_Abstract
{
    protected $_optionLabels = null;

    public function getJsonConfig()
    {
        // Inject collect options labels before parent's job
        $this->collectOptionLabels();

        // Then let parent do the job
        return parent::getJsonConfig();
    }

    protected function collectOptionLabels()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        /* Wigman: Let's fetch all attribute labels for this product */
        $configAttributes = Mage::getResourceModel('configurableswatches/catalog_product_attribute_super_collection')
        ->addParentProductsFilter(array($currentProduct->getId()))
        ->attachEavAttributes()
        ->setStoreId($store->getId());

        /* Wigman: and then store them into an array for reference */
        $optionLabels = array();
        foreach ($configAttributes as $attr) {
            $optionLabels += $attr->getOptionLabels();
        }

        $this->_optionLabels = $optionLabels;
    }

    // Implement sorting of labels inside validate because its cool
    protected function _validateAttributeInfo(&$info)
    {
        $ret = parent::_validateAttributeInfo($info);
        // Dont loose time if not vaid
        if($ret){

            // traverse info and append sort
            foreach($info['options'] as &$option){
                $option['sort_id'] = $this->_optionLabels[$option['id']][0]['sort_id'];
                $test ='';
            }
            //Wigman: then finally we sort the attribute options array by sort_id
            usort($info['options'], function ($a, $b) {
                return $a['sort_id'] - $b['sort_id'];
            });

        }
        return $ret;
    }
}
