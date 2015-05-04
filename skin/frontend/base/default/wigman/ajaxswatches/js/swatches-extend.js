$j(document).ready(function() {

	(function(updateImage) {
	  ConfigurableMediaImages.updateImage = function (el) {

	    updateImage.call(el);

	        var select = $j(el);
	        var label = select.find('option:selected').attr('data-label');
	        var productId = optionsPrice.productId;

	        //find all selected labels
	        var selectedLabels = new Array();

	        $j('.product-options .super-attribute-select').each(function() {
	            var $option = $j(this);
	            if($option.val() != '') {
	                selectedLabels.push($option.find('option:selected').attr('data-label'));
	            }
	        });

	        var pid = ConfigurableMediaImages.getSwatchProdId(productId, label, selectedLabels);

	        if(!pid) {
	            return;
	        }

	        $j.ajax({
				url: posturl + 'ajaxswatches/ajax/update',
				dataType: 'json',
				type : 'post',
				data: 'pid='+pid,
				success: function(data){
					if(data){
                        if(data.hasOwnProperty('update_blocks')) {
                            try{
                                data.update_blocks.each(function(block){
                                    if(block.key) {
                                        var dom_selector = block.key;
                                        if($$(dom_selector)) {
                                            $$(dom_selector).each(function(e){
                                                $(e).replace(block.value);
                                            });
                                        }
                                    }
                                });
                            } catch(e) {
                                //console.log(e);
                            }
                        }

                        if(data.hasOwnProperty('media_images')){
                            ConfigurableMediaImages.setMoreImages(data.media_images);
                        }
					} else {
						return true;
					}
				}
			});


	  };
	}(ConfigurableMediaImages.updateImage));

});

ConfigurableMediaImages.ajaxLoadSwatchList = function(){

	if(typeof(ConfigurableSwatchesList) != 'undefined'){

		var items = $j('.products-grid li.item,.products-list li.item');
		var i = 0;

		//we allow the activeSwatch to be defined globally for compatibility with for example Mana filters (defined in wigman_ajaxswatches.xml)
		if(typeof activeSwatchSelector === 'undefined'){
			activeSwatchSelector = '.swatch-current .value img'; //default selector
		}
		var activeSwatch = $j(activeSwatchSelector);

		var pids = [];
		var viewMode = ($j('#products-list').length>0) ? 'list':'grid';

		items.find('.product-image img').each(function(){

			var target = $j(this);
			pids.push(target.attr('id').split('-').pop());

			target.parentsUntil('ul,ol').find('.product-name').after('<div class="swatch-loader" style="text-align:center;"><img src="'+posturl+'skin/frontend/base/default/wigman/ajaxswatches/images/ajax-loader.gif" width="17" height="17" style="display:inline;" /></div>');
		});

		$j.ajax({
				url: posturl + 'ajaxswatches/ajax/getlistdata',
				dataType: 'json',
				type : 'post',
				data: 'pids='+pids.join(',')+'&viewMode='+viewMode,
				success: function(data){
					if(data){

						if(data.swatches){

							$j(data.swatches).each(function(key, swatchObj){
								i++;
								// It's not valid HTML having multiple elements with same ID
								// but in some cases there are same products on one page (e.g. top seller slider)
								var parentLi = $j('[id="product-collection-image-' + swatchObj['id'] + '"]').parentsUntil('ul,ol');

								//$j(swatchObj['value']).insertAfter(parentLi.find('.product-name'));
								parentLi.find('.swatch-loader').replaceWith($j(swatchObj['value']));
								if(i == items.length){
									if(activeSwatch.length){

										items.find(".configurable-swatch-list li[data-option-label='"+activeSwatch.attr('title')
											.toLowerCase()+"']")
											.addClass('filter-match');

									}
									ConfigurableMediaImages.ajaxInit(data.jsons);
								}
							})
						}
					} else {
						//return false;
					}
				}
			});

    }
}

$j(document).on('product-media-loaded', function() {

	ConfigurableMediaImages.ajaxLoadSwatchList();

});

ConfigurableMediaImages.ajaxInit = function(jsons){

	ConfigurableMediaImages.init('small_image');

	for (var key in jsons) {
		ConfigurableMediaImages.setImageFallback(key, $j.parseJSON(jsons[key]));
	}

	$j(document).trigger('configurable-media-images-init', ConfigurableMediaImages);
}


ConfigurableMediaImages.setMoreImages = function(data){

	var newImages = Array();
	var maxId = 0;

	var thumblist = $j('.product-image-thumbs');
	var gallery   =	$j('.product-image-gallery');

	thumblist.find('li').each(function(){ //removing current thumbs and large images
		$j('#image-'+$j(this).find('a').data('image-index')).remove();
		$j(this).remove();
	});

	$j.each(data, function(key, value){ //adding new images

		maxId++;

		thumblist.append('<li><a class="thumb-link" href="#" title data-image-index="'+maxId+'"><img src="'+value['thumb']+'" width="75" height="75" alt=""></a></li>');
		gallery.append('<img id="image-'+maxId+'" class="gallery-image" src="'+value['image']+'" data-zoom-image="'+value['image']+'">');
	});
	ProductMediaManager.wireThumbnails();

}


ConfigurableMediaImages.getSwatchProdId = function(productId, optionLabel, selectedLabels) {
        var fallback = ConfigurableMediaImages.productImages[productId];
        if(!fallback) {
            return null;
        }

		var compatibleProducts = ConfigurableMediaImages.getCompatibleProductImages(fallback, selectedLabels);

        if(compatibleProducts.length == 0) { //no compatible products
            return null; //bail
        }


        var childSwatchProdId = null;
        var childProductImages = fallback[ConfigurableMediaImages.imageType];
        compatibleProducts.each(function(productId) {
            if(childProductImages[productId] && ConfigurableMediaImages.isValidImage(childProductImages[productId])) {
                childSwatchProdId = productId;
                return false; //break "loop"
            }
        });
        if (childSwatchProdId) {
            return childSwatchProdId;
        }

        //fourth, get base image off parent product
        if (childProductImages[productId] && ConfigurableMediaImages.isValidImage(childProductImages[productId])) {
            return productId;
        }

        //no prodId found
        return null;
}