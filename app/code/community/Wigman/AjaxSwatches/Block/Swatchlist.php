<?php
class Wigman_AjaxSwatches_Block_Swatchlist extends Mage_Core_Block_Template
{
	public $product, $products;
	
	protected function _construct() {
		parent::_construct();
	}
	
	protected function _toHtml() {
	
		$pid = $this->getPid();
		
		$storeId = Mage::App()->getStore()->getId();
		
		$collection = Mage::getModel('catalog/product')->setStoreId($storeId)->getCollection()
		   ->addAttributeToSelect('*')
		   ->addAttributeToFilter('entity_id', $pid)
		   ->addStoreFilter($storeId)
		   ->load();
	
	    /* @var $helper Mage_ConfigurableSwatches_Helper_Mediafallback */
	    $helper = Mage::helper('configurableswatches/mediafallback');
		
	    if ($collection
	        instanceof Mage_ConfigurableSwatches_Model_Resource_Catalog_Product_Type_Configurable_Product_Collection) {
	        // avoid recursion
	        return;
	    }
	
	    $products = $collection->getItems();
		
	    $helper->attachChildrenProducts($products, $collection->getStoreId());
	
	    $helper->attachConfigurableProductChildrenAttributeMapping($products, $collection->getStoreId());
	
	    $helper->attachGallerySetToCollection($products, $collection->getStoreId());
		
		/* @var $product Mage_Catalog_Model_Product */
		foreach ($products as $product) { //only runs once
			$helper->groupMediaGalleryImages($product);
			Mage::helper('configurableswatches/productimg')
			    ->indexProductImages($product, $product->getListSwatchAttrValues());

			$this->product = $product;
		}	
		
		$this->products = $products;
		
		return parent::_toHtml();
	}
	
	public function getCollection()
    {

		return $this->products;
	}
	
    public function getProduct()
    {
	    return $this->product;
	}

    protected function _isCacheActive()
    {
        if (!Mage::getStoreConfig('catalog/frontend/cache_list')) {
            return false;
        }

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
		//Mage::log($this->getPid());
        $cacheKey = 'SwatchList_'.
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
            Mage_Catalog_Model_Product::CACHE_TAG.'_'.$this->getPid()
        );

        return $cacheTags;

    }
    
}