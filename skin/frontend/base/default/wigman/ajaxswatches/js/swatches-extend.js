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
						ConfigurableMediaImages.setMoreImages(data);
					} else {
						return true;
					}
				}
			});
			
	      	
	  };
	}(ConfigurableMediaImages.updateImage));

});

$j(document).on('product-media-loaded', function() {

	if(typeof(ConfigurableSwatchesList) != 'undefined'){
			
		var items = $j('.products-grid li.item,.products-list li.item');
		var i = 0;
		var activeSwatch = $j('.swatch-current .value img');
	
		var pids = [];
		
		items.find('.product-image img').each(function(){
			
			var target = $j(this);
			pids.push(target.attr('id').split('-').pop());

		});
		
		$j.ajax({
				url: posturl + 'ajaxswatches/ajax/getlistdata',
				dataType: 'json',
				type : 'post',
				data: 'pids='+pids.join(','),
				success: function(data){
					if(data){

						if(data.swatches){

							$j(data.swatches).each(function(key, swatchObj){
								i++;
								var parentLi = $j('#product-collection-image-'+swatchObj['id']).parentsUntil('ul');
								
								$j(swatchObj['value']).insertAfter(parentLi.find('.product-name'));
						
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