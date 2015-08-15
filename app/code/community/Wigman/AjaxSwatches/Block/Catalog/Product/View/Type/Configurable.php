<?php
/*/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog super product configurable part block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
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
