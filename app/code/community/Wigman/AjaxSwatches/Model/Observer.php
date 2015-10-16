<?php
class Wigman_AjaxSwatches_Model_Observer extends Mage_ConfigurableSwatches_Model_Observer
{
    /**
     * Attach children products after product list load
     * Observes: catalog_block_product_list_collection
     *
     * @param Varien_Event_Observer $observer
     */
    public function productListCollectionLoadAfter(Varien_Event_Observer $observer)
    {
        return; // disable this entire functionality because it's slowwwwww
    }
}
