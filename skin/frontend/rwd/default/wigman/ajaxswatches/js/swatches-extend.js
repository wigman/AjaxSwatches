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
						setMoreImages(data);
					} else {
						return false;
					}
				}
			});
			
	      	
	  };
	}(ConfigurableMediaImages.updateImage));

});

function setMoreImages(data){
	
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
		
		//console.log('set new image with id '+maxId+' and thumb '+ value['thumb']);
		
		thumblist.append('<li><a class="thumb-link" href="#" title data-image-index="'+maxId+'"><img src="'+value['thumb']+'" width="75" height="75" alt=""></a></li>');
		gallery.append('<img id="image-'+maxId+'" class="gallery-image" src="'+value['image']+'" data-zoom-image="'+value['image']+'">');
	});
	ProductMediaManager.wireThumbnails();

}