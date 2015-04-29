<?php

class Wigman_AjaxSwatches_AjaxController extends Mage_Core_Controller_Front_Action
{

    public function updateAction()
    {
        $pid = $this->getRequest()->getParam('pid', null);
        if (is_null($pid)) {
            return;
        }

        // get Product
        $_product = Mage::getModel('catalog/product')->load($pid);

        // register product otherwise each child template won't work
        Mage::unregister('product');
        Mage::register('product', $_product);

        // load layout for catalog product view page
        $this->loadLayout('catalog_product_view');
        $layout = $this->getLayout();
        $response = array();

        // get blocks which should be updated
        $updateBlocks = $layout->getBlock('updateBlocks')->getData('blocks');
        $_blockHtml = array();

        if($updateBlocks) {
            foreach ($updateBlocks as $key => $blockInfo) {
                $block = $layout->getBlock($blockInfo['name']);
                if($block) {
                    $_blockHtml[] = array(
                        'key' => $blockInfo['selector'],
                        'value' => $block->setProduct($_product)->toHtml()
                    );
                }
            }
            $response['update_blocks'] = $_blockHtml;
        }

        $mediaImages= $_product->getMediaGalleryImages();
        if($mediaImages->count()) {
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
                }
            }
            $response['media_images'] = $images;
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function getlistdataAction()
    {

        if (!isset($_REQUEST['pids'])) {
            exit;
        }

        if (!Mage::helper('configurableswatches')->isEnabled()) { // check if functionality disabled
            exit; // exit without loading swatch functionality
        }

        $pids = explode(',', $_REQUEST['pids']);

        $response = $swatches = $jsons = array();
        $this->loadLayout();

        $viewMode = (isset($_REQUEST['viewMode'])) ? $_REQUEST['viewMode'] : 'grid';
        $keepFrame = ($viewMode == 'grid') ? true : false;

        foreach ($pids as $pid) {

            Mage::unregister('catViewKeepFrame');
            Mage::register('catViewKeepFrame', $keepFrame);


            $swatches[] = array('id' => $pid, 'value' => $this->getLayout()
                ->createBlock('Wigman_AjaxSwatches/swatchlist', 'swatchlist-' . $pid)
                ->setPid($pid)
                ->setViewMode($viewMode)
                ->setTemplate('configurableswatches/catalog/product/list/swatches.phtml')
                ->toHtml());

            $productsCollection = $this->getLayout()->getBlock('swatchlist-' . $pid)->getCollection();

            //Mage::log($productsCollection);

            $jsons[$pid] = $this->getLayout()
                ->createBlock('Wigman_AjaxSwatches/catalog_media_js_list', 'mediajslist-' . $pid)
                ->setPid($pid)
                ->setViewMode($viewMode)
                ->setProductCollection($productsCollection)
                ->setTemplate('wigman/ajaxswatches/media/js.phtml')
                ->toHtml();

        }

        $response['swatches'] = $swatches;

        $response['jsons'] = $jsons;

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

}