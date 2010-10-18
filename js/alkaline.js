var BASE = $('meta[name="base"]').attr('content');
var FOLDER_PREFIX = $('meta[name="folder_prefix"]').attr('content');

var ADMIN = FOLDER_PREFIX + 'admin/';
var IMAGES = FOLDER_PREFIX + 'images/';
var PHOTOS = FOLDER_PREFIX + 'photos/';

var working = 0;

function process(){
	gallery = $('#gallery').html();
	gallery = gallery.replace(/%7B/g, "{");
	gallery = gallery.replace(/%7D/g, "}");
	photo_count = photos.length;
	first();
}

function first(){
	update(0);
}

function last(){
	update(photo_count - 1);
}

function next(){
	if((photo_id + 1) < photo_count){
		update(++photo_id);
		$('#gallery').fadeIn();
	}
	else{
		first();
	}
}

function prev(){
	if(photo_id > 0){
		update(--photo_id);
	}
	else{
		last();
	}
}

function reset(){
	photo = $('#gallery').find('img');
	height = photo.innerHeight();
    width = photo.innerWidth();
	doc_height = $(document).height();
	if(doc_height > 700){
		if(width > height){
			padding = ((640 - height) / 2) + ((doc_height - 700) / 2);
		}
		else{
			padding = ((doc_height - 700) / 2);
		}
	}
	else{
		if(width > height){
			padding = ((640 - height) / 2);
		}
		else{
			padding = 0;
		}
	}
	$('#gallery').css('padding-top', padding + 'px');
}

function update(id){
	if(working == 0){
		working = 1;
		photo_id = id;
	
		var field_regex = new RegExp('\{(.*?)\}', 'gi');
	
		function field(field, photo_id){
			field = field.replace(field_regex, "$1").toLowerCase();
			field = photos[photo_id][field];
			if(field == null){ field = ''; }
			return field;
		}
	
		gallery_copy = gallery;
	
		while((matchArray = field_regex.exec(gallery_copy)) != null) {
			replacement = field(matchArray[0], photo_id);
			gallery_copy = gallery_copy.replace(matchArray[0], replacement);
		}
	
		$('#gallery').fadeOut(100, function(){ $('#gallery').html(gallery_copy); }).delay(0).hide(0, function(){ reset(); }).fadeIn(100, function(){ working = 0; reset(); preload(); });
		
		function preload(){
			if((photo_count - photo_id) < 5){
				photo_id_top = photo_count;
			}
			else{
				photo_id_top = photo_id + 4;
			}
		
			gallery_copy_cumulative = '';
		
			$('.preload').remove();
		
			for(var photo_id_temp = (photo_id + 1); photo_id_temp < photo_id_top; photo_id_temp++){
				gallery_copy = gallery;

				while((matchArray = field_regex.exec(gallery_copy)) != null) {
					replacement = field(matchArray[0], photo_id_temp);
					gallery_copy = gallery_copy.replace(matchArray[0], replacement);
				}
				gallery_copy_cumulative += gallery_copy;
			}
			$('<div class="preload">' + gallery_copy_cumulative + '</div>').appendTo('body');
		}
	}
}

$(document).ready(function(){
	if($(document).has('#gallery').length){
		$('#gallery').hide();
	 	var gallery = true;
	}
	
	$(window).resize(function () { reset(); });
	
	if(gallery == true){
		$.get(BASE + ADMIN + "tasks/build-slideshow.php", function(data){ photos = $.evalJSON(data).photos; process(); } );
		$(document).keydown(function(event){
			if(event.keyCode == '37'){
				prev();
			}
			if(event.keyCode == '39'){
				next();
			}
		});
	}
});

$(window).load(function(){
	reset();
});