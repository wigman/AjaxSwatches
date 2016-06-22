# ***ONLY WORKS WITH RWD THEME***

# Wigman AjaxSwatches

Upgrade notice:
When upgrading from versions before 0.3, please remove 
* app/code/local/wigman
* skin/frontend/rwd/wigman
* app/design/frontend/rwd/default/layout/wigman_ajaxswatches.xml
* app/design/frontend/rwd/default/template/wigman

Known bugs: currently, none. Yay!

### version
0.4.4 Release notes:
* Adds check to product list pages wether a product is configurable and contains an attribute defined as swatch from admin (so less needless requests on the serverside
* The loader placeholder no longer comes from javascript but is now implemented (including above check) in app/design/frontend/base/default/template/wigman/ajaxswatches/catalog/product/list/swatches.phtml 
* bugfixes to the product view pages when multiple attributes are used.

0.4.3 Release notes:
* Composer added by Brandung GmbH & Co. KG

0.4.2 Release notes:
* Fixed image constraint in grid/list mode.
* Added support for custom layered navigation modules (like ManaDev). See point #4 below for detailed info.

0.4.1 Release notes:
* Bug fix when no store-specific labels are defined.

0.4.0 Release notes:
* Added attribute sorting by admin position
* Swatches are now respecting the admin setting 'Display Out of Stock Products' option that can be set at System->Configuration->Catalog->Inventory->Stock Options (cataloginventory/options/show_out_of_stock)
* Fixed a major bug preventing the swatches from being cached (many thanks to Lion Web Inc. for contributing to the fix)
* We're now showing a loader images where the swatches are supposed to pop-up after load. Because it could take a second if the swatches haven't been cached yet

0.3.0 Release notes:
* moved code pool to community (requested by Simon Sprankel)
* moved theme files to /base folder since some themes are making the swatches backwards compatible
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


### 4. Add support for Custom layered navigation.

When using a custom layered navigation module, chances are that the jQuery selector that determines what swatches are active is malfunctioning.
Therefore you can (as of version 0.4.2) set a custom jQuery selector in the layout file.

How to change the jQuery selector:
Copy the file /app/design/frontend/base/default/layout/wigman_ajaxswatches.xml to your custom theme -> /app/design/frontend/[YOUR DESIGN PACKAGE]/[YOUR THEME]/layout/wigman_ajaxswatches.xml

Change '.swatch-current .value img' on line 39 into your custom selector:

			<block type="core/template" name="baseurl" template="wigman/ajaxswatches/baseurl.phtml">
			    <action method="setData"><name>active_swatch_selector</name><value><![CDATA[.swatch-current .value img]]></value></action>
			</block>

For example, if you are using ManaDev's layered navigation, the selector would become "input[id^=filter_left_color][checked=checked] ~ label span"

This selector pickes the &lt;label&gt;&lt;span&gt;Attribute Label&lt;/span&gt;&lt;/label&gt; that comes after a checked &lt;input id=&quot;filter_left_color_1234&quot;&gt; element.

			<li class="m-selected-ln-item">
				<input type="checkbox" id="filter_left_color_1234" value="1000" checked="checked" onclick="setLocation('http://www.url.com');">
				<label for="filter_left_color_1234"><span class="m-selected-checkbox-item" title="Black">Black</span></label>
			</li>

You will probably need to change "filter_left_color" to reflect your attribute name (like filter_left_kleur or filter_left_farbe) if you run a non-english store.

Also, if using multiple store-views you could chain selectors like: "input[id^=filter_left_color][checked=checked] ~ label span,input[id^=filter_left_kleur][checked=checked] ~ label span".

Or you could create a separate layout file per store theme design folder:
* /app/design/frontend/[YOUR DESIGN PACKAGE]/[YOUR THEME ENGLISH]/layout/wigman_ajaxswatches.xml
* /app/design/frontend/[YOUR DESIGN PACKAGE]/[YOUR THEME GERMAN]/layout/wigman_ajaxswatches.xml
