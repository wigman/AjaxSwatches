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
class Wigman_AjaxSwatches_Model_Resource_Catalog_Product_Attribute_Super_Collection
    extends Mage_ConfigurableSwatches_Model_Resource_Catalog_Product_Attribute_Super_Collection
{

    /**
     * Load attribute option labels for current store and default (fallback)
     *
     * @return $this
     *
     * Wigman added sort_id
     */
    protected function _loadOptionLabels()
    {
        if ($this->count()) {
            $select = $this->getConnection()->select()
                ->from(
                    array('attr' => $this->getTable('catalog/product_super_attribute')),
                    array(
                        'product_super_attribute_id' => 'attr.product_super_attribute_id',
                    ))
                ->join(
                    array('opt' => $this->getTable('eav/attribute_option')),
                    'opt.attribute_id = attr.attribute_id',
                    array(
                        'attribute_id' => 'opt.attribute_id',
                        'option_id' => 'opt.option_id',
                        'sort_order' => 'opt.sort_order', //Wigman: added
                    ))
                ->join(
                    array('lab' => $this->getTable('eav/attribute_option_value')),
                    'lab.option_id = opt.option_id',
                    array(
                        'label' => 'lab.value',
                        'store_id' => 'lab.store_id',
                    ))
                ->where('attr.product_super_attribute_id IN (?)', array_keys($this->_items))
            ;

            $result = $this->getConnection()->fetchAll($select);
            foreach ($result as $data) {
                $item = $this->getItemById($data['product_super_attribute_id']);
                if (!is_array($labels = $item->getOptionLabels())) {
                    $labels = array();
                }
                $labels[$data['option_id']][$data['store_id']] = array('label' => $data['label'], 'sort_id' => $data['sort_order']); //Wigman: added array dimension to include sort_id
                $item->setOptionLabels($labels);
            }
        }
        return $this;
    }
}
