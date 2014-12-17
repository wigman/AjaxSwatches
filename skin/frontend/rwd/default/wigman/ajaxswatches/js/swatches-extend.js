$j(document).ready(function() {

	(function(updateImage) {
	  ConfigurableMediaImages.updateImage = function (el) {
	    
	    updateImage.call(el);
	    
	        var select = $j(el);
	        var label = select.find('option:selected').attr('data-label');
	        var productId = optionsPrice.productId;
	         
	        var pid = ConfigurableMediaImages.productImages[productId].option_labels[label].products[0];
	        
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
								var parentLi = $j('#product-collection-image-'+swatchObj['id']).parent().parent();
								
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