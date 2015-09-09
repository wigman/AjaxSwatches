<?php 
class Wigman_AjaxSwatches_AjaxController extends Mage_Core_Controller_Front_Action {

protected $_request;

protected function _construct()
{
	parent::_construct();
	$this->_request = Mage::app()->getRequest()->getParams();
}

public function updateAction(){


if(!isset($this->_request['pid'])) { return; }

$pid = $this->_request['pid'];

$_product = Mage::getModel('catalog/product')->load($pid);
//get Product


$mediaImages= $_product->getMediaGalleryImages();

$images = array();
$i=1;
foreach ($mediaImages as $_image){
	//var_dump($image);
	if(!$_image['disabled_default']){
		
		//echo Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize(75);
		$newImage = array(
			'thumb' => (string) Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize(75),
			'image' => (string) Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile())
		);
		
		
		$images[$i] = $newImage;
		$i++;
	} else {
		//echo 'image disabled';
	}
	
}


$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($images));
return;	

//return $images;

}

public function getlistdataAction(){

if(!isset($this->_request['pids'])) { return; }

if (!Mage::helper('configurableswatches')->isEnabled()) { // check if functionality disabled
    return; // return without loading swatch functionality
}

$pids = explode(',',$this->_request['pids']);

$response = $swatches = $jsons = array();
$this->loadLayout();

$viewMode = (isset($this->_request['viewMode']))? $this->_request['viewMode'] : 'grid';
$keepFrame = ($viewMode == 'grid')? true : false;

foreach($pids as $pid){
	
	Mage::unregister('catViewKeepFrame');
	Mage::register('catViewKeepFrame',$keepFrame);
	 	
	 	
	$swatches[] = array('id' => $pid, 'value' => $this->getLayout()
				->createBlock('Wigman_AjaxSwatches/swatchlist','swatchlist-'.$pid)
				->setPid($pid)
				->setViewMode($viewMode)
				->setTemplate('configurableswatches/catalog/product/list/swatches.phtml')
				->toHtml());
	
	$productsCollection = $this->getLayout()->getBlock('swatchlist-'.$pid)->getCollection();
	
	//Mage::log($productsCollection);
	
	$jsons[$pid] = $this->getLayout()
				->createBlock('Wigman_AjaxSwatches/catalog_media_js_list','mediajslist-'.$pid)
				->setPid($pid)
				->setViewMode($viewMode)
				->setProductCollection($productsCollection)
				->setTemplate('wigman/ajaxswatches/media/js.phtml')
				->toHtml();
					
}
		
	    $response['swatches'] = $swatches;
		
		$response['jsons'] = $jsons;
		
		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
}

}