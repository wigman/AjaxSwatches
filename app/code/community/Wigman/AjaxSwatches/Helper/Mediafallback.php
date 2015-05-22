<?php
/**
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
 * @package     Mage_ConfigurableSwatches
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class implementing the media fallback layer for swatches
 */
class Wigman_AjaxSwatches_Helper_Mediafallback extends Mage_ConfigurableSwatches_Helper_Mediafallback
{

    /**
     * Set child_attribute_label_mapping on products with attribute label -> product mapping
     * Depends on following product data:
     * - product must have children products attached
     *
     * @param array $parentProducts
     * @param $storeId
     * @return void
     *
     * Wigman added: hide colors when 'show_out_of_stock' is set to false in admin (cataloginventory/options/show_out_of_stock)
     *
     */
    public function attachConfigurableProductChildrenAttributeMapping(array $parentProducts, $storeId = 0)
    {
        $listSwatchAttr = Mage::helper('configurableswatches/productlist')->getSwatchAttribute();

        $parentProductIds = array();
        /* @var $parentProduct Mage_Catalog_Model_Product */
        foreach ($parentProducts as $parentProduct) {
            $parentProductIds[] = $parentProduct->getId();
        }

        $configAttributes = Mage::getResourceModel('configurableswatches/catalog_product_attribute_super_collection')
            ->addParentProductsFilter($parentProductIds)
            ->attachEavAttributes()
            ->setStoreId($storeId)
        ;

        $optionLabels = array();
        foreach ($configAttributes as $attribute) {
            $optionLabels += $attribute->getOptionLabels();
        }
        foreach ($parentProducts as $parentProduct) {
            $mapping = array();
            $listSwatchValues = array();

            /* @var $attribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            foreach ($configAttributes as $attribute) {
                /* @var $childProduct Mage_Catalog_Model_Product */
                if (!is_array($parentProduct->getChildrenProducts())) {
                    continue;
                }
                foreach ($parentProduct->getChildrenProducts() as $childProduct) {

                    // product has no value for attribute, we can't process it
                    if (!$childProduct->hasData($attribute->getAttributeCode())) {
                        continue;
                    }
                    
                    if (!Mage::getStoreConfig('cataloginventory/options/show_out_of_stock') && !$childProduct->isSalable()) {
                        continue;
                    }

                    $optionId = $childProduct->getData($attribute->getAttributeCode());

                    // if we don't have a default label, skip it
                    if (!isset($optionLabels[$optionId][0]['label'])) {
                        continue;
                    }
					
                    // normalize to all lower case before we start using them
                    $optionLabels = array_map(function ($value) {
                        return array_map(function($key){
	                        $key['label'] = trim(strtolower($key['label']));
							return $key;
                        }, $value);
                    }, $optionLabels);
					
                    // using default value as key unless store-specific label is present
                    $optionLabel = $optionLabels[$optionId][0]['label'];
                    $sortId = $optionLabels[$optionId][0]['sort_id'];
                    if (isset($optionLabels[$optionId][$storeId])) {
                        $optionLabel = $optionLabels[$optionId][$storeId]['label'];
	                    $sortId = $optionLabels[$optionId][$storeId]['sort_id'];
                    }

                    // initialize arrays if not present
                    if (!isset($optionLabel) || !isset($mapping[$optionLabel])) {
                        $mapping[$optionLabel] = array(
                            'product_ids' => array(),
                        );
                    }

                    $mapping[$optionLabel]['product_ids'][] = $childProduct->getId();
                    $mapping[$optionLabel]['label'] = $optionLabel;
                    $mapping[$optionLabel]['default_label'] = $optionLabels[$optionId][0]['label'];
                    $mapping[$optionLabel]['labels'] = $optionLabels[$optionId];
                    $mapping[$optionLabel]['sort_id'] = $sortId;

                    if ($attribute->getAttributeId() == $listSwatchAttr->getAttributeId()
                        && !in_array($mapping[$optionLabel]['label'], $listSwatchValues)
                    ) {
                        $listSwatchValues[$optionId] = $mapping[$optionLabel]['label'];
                    }
                } // end looping child products
            } // end looping attributes

            foreach ($mapping as $key => $value) {
                $mapping[$key]['product_ids'] = array_unique($mapping[$key]['product_ids']);
            }
            
            uasort($listSwatchValues, function($a, $b) use ($mapping) {
				return $mapping[$a]['sort_id'] - $mapping[$b]['sort_id'];
			});

            $parentProduct->setChildAttributeLabelMapping($mapping)
                ->setListSwatchAttrValues($listSwatchValues);
        } // end looping parent products
    }

}
