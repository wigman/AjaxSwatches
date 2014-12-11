<?php 
class Wigman_AjaxSwatches_AjaxController extends Mage_Core_Controller_Front_Action {

public function updateAction(){


if(!isset($_REQUEST['pid'])) { exit; }

$pid = $_REQUEST['pid'];

$_product = Mage::getModel('catalog/product')->load($pid);

$mediaImages= $_product->getMediaGalleryImages();

$images = array();
$i=1;
foreach ($mediaImages as $_image){

	if(!$_image['disabled_default']){
		
		$newImage = array(
			'thumb' => (string) Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize(75),
			'image' => (string) Mage::helper('catalog/image')->init($_product, 'image', $_image->getFile())
		);
		
		
		$images[$i] = $newImage;
		$i++;
	} else {
		//Image is disabled in admin and will not be shown
	}
	
}

$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($images));
return;	

}}

?> 