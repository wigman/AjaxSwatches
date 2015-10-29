<?php
class Wigman_AjaxSwatches_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function updateAction()
    {
        $_pid = $this->getRequest()->getParam('pid');
        if (! $_pid) throw new Exception();

        $_product = Mage::getModel('catalog/product')->load($_pid);

        $_imageHelper = Mage::helper('catalog/image');

        $_images = array();
        foreach ($_product->getMediaGalleryImages() as $_image) {
            if ($_image['disabled_default']) continue;

            $_imageFile = $_image->getFile();

            $_images[] = array(
                'thumb' => (string) $_imageHelper->init($_product, 'thumbnail', $_imageFile)->resize(75),
                'image' => (string) $_imageHelper->init($_product, 'image', $_imageFile)
            );
        }

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type','application/json',true)
            ->setBody(Mage::helper('core')->jsonEncode($_images));
    }

    public function getlistdataAction()
    {
        $_request = $this->getRequest();

        // check if functionality disabled
        if (! Mage::helper('configurableswatches')->isEnabled()) {
            throw new Exception('Configurable swatches disabled.');
        }

        $_pids = explode(',', $_request->getParam('pids'));
        if (empty($_pids)) {
            throw new Exception('Missing "pids" post parameter.');
        }

        $this->loadLayout();

        $_viewMode = ($_request->getParam('viewMode')) ? $_request->getParam('viewMode') : 'grid';
        Mage::unregister('catViewKeepFrame');
        Mage::register('catViewKeepFrame', $_viewMode == 'grid');

        $_response = array();
        foreach ($_pids as $_pid) {
            $_response['swatches'][] = array(
                'id' => $_pid,
                'value' => $this->getLayout()
                    ->createBlock('Wigman_AjaxSwatches/swatchlist', "swatchlist-$_pid")
                    ->setPid($_pid)
                    ->setViewMode($_viewMode)
                    ->setTemplate('configurableswatches/catalog/product/list/swatches.phtml')
                    ->toHtml()
            );

            $_response['jsons'][$_pid] = $this->getLayout()
                ->createBlock('Wigman_AjaxSwatches/catalog_media_js_list', "mediajslist-$_pid")
                ->setPid($_pid)
                ->setViewMode($_viewMode)
                ->setProductCollection($this->getLayout()->getBlock("swatchlist-$_pid")->getCollection())
                ->setTemplate('wigman/ajaxswatches/media/js.phtml')
                ->toHtml();
        }

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type', 'application/json', true)
            ->setBody(Mage::helper('core')->jsonEncode($_response));
    }
}
