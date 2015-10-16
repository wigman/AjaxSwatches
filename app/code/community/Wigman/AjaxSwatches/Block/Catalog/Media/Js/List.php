<?php
class Wigman_AjaxSwatches_Block_Catalog_Media_Js_List extends Mage_ConfigurableSwatches_Block_Catalog_Media_Js_List
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _isCacheActive()
    {
        /* if there are any messages dont read from cache to show them */
        if (Mage::getSingleton('core/session')->getMessages(true)->count() > 0) {
            return false;
        }
        return true;
    }

    public function getCacheLifetime()
    {
        if ($this->_isCacheActive()) {
            return false;
        }
    }

    public function getCacheKey()
    {
        if (!$this->_isCacheActive()) {
            parent::getCacheKey();
        }

        $cacheKey = 'MediaJsList_'.
            /* Create different caches for different categories */
            $this->getPid().'_'.
            /* ... stores */
            Mage::App()->getStore()->getCode().'_'.
            '';

        return $cacheKey;
    }

    public function getCacheTags()
    {
        if (!$this->_isCacheActive()) {
            return parent::getCacheTags();
        }
        $cacheTags = array(
            Mage_Catalog_Model_Product::CACHE_TAG,
            Mage_Catalog_Model_Product::CACHE_TAG."_".$this->getPid()
        );

        return $cacheTags;
    }

}
