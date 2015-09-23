<?php 
class Wigman_AjaxSwatches_AjaxController extends Mage_Core_Controller_Front_Action {

public function updateAction(){


if(!Mage::app()->getRequest()->getParam('pid')) { return; }

$pid = Mage::app()->getRequest()->getParam('pid');

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

if(!Mage::app()->getRequest()->getParam('pids')) { return; }

if (!Mage::helper('configurableswatches')->isEnabled()) { // check if functionality disabled
    return; // return without loading swatch functionality
}

$pids = explode(',',Mage::app()->getRequest()->getParam('pids'));

$response = $swatches = $jsons = array();
$this->loadLayout();

$viewMode = (Mage::app()->getRequest()->getParam('viewMode'))? Mage::app()->getRequest()->getParam('viewMode') : 'grid';
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
