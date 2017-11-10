<?php
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

    /* comment this function to fix ajax swatches for Magento 1.9.3
    /***************************************************************

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
    */
}
