<?php
if ((string)Mage::getConfig()->getModuleConfig('OrganicInternet_SimpleConfigurableProducts')->active == 'true'){
    class Wigman_AjaxSwatches_Block_Catalog_Product_View_Type_Configurable_Abstract extends OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable {}
} else {
    class Wigman_AjaxSwatches_Block_Catalog_Product_View_Type_Configurable_Abstract extends Mage_Catalog_Block_Product_View_Type_Configurable {}
}