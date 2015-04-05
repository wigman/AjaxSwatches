<?php 
class Wigman_AjaxSwatches_AjaxController extends Mage_Core_Controller_Front_Action {

public function updateAction(){


if(!isset($_REQUEST['pid'])) { exit; }

$pid = $_REQUEST['pid'];

$_product = Mage::getModel('catalog/product')->load($pid);
//get Product


/*
$this->loadLayout();
    $sidebar = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
    $response['sidebar'] = $sidebar;
*/

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

//var_dump($images);
//echo Mage::helper('core')->jsonEncode($images);

$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($images));
return;	

//return $images;

}

public function getlistdataAction(){

if(!isset($_REQUEST['pids'])) { exit; }

if (!Mage::helper('configurableswatches')->isEnabled()) { // check if functionality disabled
    exit; // exit without loading swatch functionality
}

$pids = explode(',',$_REQUEST['pids']);

$response = $swatches = $jsons = array();
$this->loadLayout();

 		
foreach($pids as $pid){
	$swatches[] = array('id' => $pid, 'value' => $this->getLayout()
				->createBlock('Wigman_AjaxSwatches/swatchlist','swatchlist-'.$pid)
				->setPid($pid)
				->setTemplate('configurableswatches/catalog/product/list/swatches.phtml')
				->toHtml());
	
	$productsCollection = $this->getLayout()->getBlock('swatchlist-'.$pid)->getCollection();
	
	//Mage::log($productsCollection);
	
	$jsons[$pid] = $this->getLayout()
				->createBlock('Wigman_AjaxSwatches/catalog_media_js_list','mediajslist-'.$pid)
				->setPid($pid)
				->setProductCollection($productsCollection)
				->setTemplate('wigman/ajaxswatches/media/js.phtml')
				->toHtml();
					
}
		
	    $response['swatches'] = $swatches;
		
		$response['jsons'] = $jsons;
		
		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
		return;	
}
}

?> 