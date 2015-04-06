# Wigman AjaxSwatches

Upgrade notice:
Please don't test on live environment since there have been some bug reports on custom themes (currently investigating).
When upgrading from previous versions, please remove app/code/local/wigman and skin/frontend/rwd/wigman.

### version
0.4.0

0.4.0 Release notes:
* Added attribute sorting by admin position
* Swatches are now respecting the admin setting 'Display Out of Stock Products' option that can be set at System->Configuration->Catalog->Inventory->Stock Options (cataloginventory/options/show_out_of_stock)
* Fixed a major bug preventing the swatches from being cached (many thanks to Lion Web Inc. for contributing to the fix)
* We're now showing a loader images where the swatches are supposed to pop-up after load. Because it could take a second if the swatches haven't been cached yet

0.3.0 Release notes:
* moved code pool to community (requested by Simon Sprankel)
* moved theme files to /base folder since some themes are making the swatches backwards compatible (like Ultimo)
* changed a jQuery selector to match product-list items on a wider scale of themes

For support or requests contact us through http://e-tailors.nl/contact

### 1. Adding Ajax functionality to the product-list pages (categories) to speed up the initial page load.
In cases where you have many product-options this can speed up the pageload 10 times!

If you have many colors/swatches in your RWD shop, you need this.

More info on this functionality here, with a test-case: http://e-tailors.nl/ajaxswatches-extension/



### 2. Adding Ajax functionality on product-view pages to Magento's RWD color swatches to switch all gallery images

DEMO: http://staging.e-tailors.nl/magentodemo/121-170-ladies-wallet-chamonix.html

This Module adds an Ajax event to the new ColorSwatches in Magento 1.9.1.0
When the event ConfigurableMediaImages.updateImage fires up, the original updateImage() function is executed.
After this we make an AJAX request to [baseurl]+'ajaxswatches/ajax/update' requesting the MediaGallery images.

The ID used to fetch the MediaGallery uses the original code from the RWD theme:

	var select = $j(el);
	var label = select.find('option:selected').attr('data-label');
	var productId = optionsPrice.productId;
	        
	var pid = ConfigurableMediaImages.productImages[productId].option_labels[label].products[0];
	

Once we've retrieved the new MediaGallery images, we remove the old thumbs and large images (for the sake of memory consumption). We might argue that it would be better to keep the downloaded images, but I chose to remove them. Second time you load the images it *should* come from browser-cache anyway.

The whole code is pretty simple and does not touch any default code. All extra images are loaded after clicking the ColorSwatches, so no extra load on page-render.

### 3. Sort attributes by admin position and hide out of stock products if set from admin

Why have these options and not use them in the default configurable swatches? Beats me.
I don't like the idea overriding too many models, but unfortunately some where necessary.

To enable attribute sorting on swatches I extended:
* Mage_Catalog_Block_Product_View_Type_Configurable
* Mage_ConfigurableSwatches_Helper_Productimg
* Mage_ConfigurableSwatches_Model_Resource_Catalog_Product_Attribute_Super_Collection

To enable admin option 'Display Out of Stock Products' set to false I extended:
* Mage_ConfigurableSwatches_Helper_Mediafallback
* Mage_ConfigurableSwatches_Helper_Productimg (was already overridden to enable sorting)
