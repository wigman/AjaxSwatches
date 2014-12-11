$j(document).ready(function() {

	(function(updateImage) {
	  ConfigurableMediaImages.updateImage = function (el) {
	    
	    updateImage.call(el);
	    
	        var select = $j(el);
	        var label = select.find('option:selected').attr('data-label');
	        var productId = optionsPrice.productId;
	        
	        //console.log(ConfigurableMediaImages.productImages[productId].option_labels[label].products[0]);
	        
	        var pid = ConfigurableMediaImages.productImages[productId].option_labels[label].products[0];
	        
	        $j.ajax({
				url: posturl + 'ajaxswatches/ajax/update',
				dataType: 'json',
				type : 'post',
				data: 'pid='+pid,
				success: function(data){
					//console.log(data);
					if(data){
						setMoreImages(data);
					} else {
						return false;
					}
				}
			});
			
	     //console.log(label);
	     	
	  };
	}(ConfigurableMediaImages.updateImage));

});

function setMoreImages(data){
	
	var newImages = Array();
	var maxId = 0;//getMaxId();
	
	var thumblist = $j('.product-image-thumbs');
	var gallery   =	$j('.product-image-gallery');
	
	thumblist.find('li').each(function(){
		console.log($j(this))
		$j('#image-'+$j(this).find('a').data('image-index')).remove();
		$j(this).remove();
	});
	
	$j.each(data, function(key, value){
		//var image = this;
		console.log(value);
		maxId++;
		
		console.log('set new image with id '+maxId+' and thumb '+ value['thumb']);
		
		thumblist.append('<li><a class="thumb-link" href="#" title data-image-index="'+maxId+'"><img src="'+value['thumb']+'" width="75" height="75" alt=""></a></li>');
		gallery.append('<img id="image-'+maxId+'" class="gallery-image" src="'+value['image']+'" data-zoom-image="'+value['image']+'">');
	});
	ProductMediaManager.wireThumbnails();

	
//	console.log(getMaxId());
//	console.log(newImages);
}

function getMaxId(){
  var max=null;
  $j('.product-image-thumbs .thumb-link').each(function() {
    var id = parseInt($j(this).data('image-index'), 10);
    if ((max===null) || (id > max)) { max = id; }
  });
  return max;
}
