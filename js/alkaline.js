var BASE = 'http://beta.alkalineapp.com/';

var ADMIN = 'admin/';
var IMAGES = 'images/';
var PHOTOS = 'photos/';

function process(){
	gallery = $('#gallery').html();
	photo_count = photos.length;
	
	for(var i = 0; i < photos.length; i++){
		// photos[i].photo_src_medium = '<img src="' + photos[i].photo_src_medium + '" />';
	}
	
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
	if(height < width){
		padding = (640 - height) / 2;
		$('#gallery').css('padding-top', padding + 'px');
	}
	else{
		$('#gallery').css('padding-top', '0');
	}
}

function update(id){
	photo_id = id;
	var field_regex = new RegExp('{(.*?)}', 'gi');
	// var title = new RegExp('\<\!\-\- PHOTO_TITLE \-\-\>', 'gi');
	
	function field(field){
		field = field.replace(field_regex, "$1").toLowerCase();
		field = photos[photo_id][field];
		if(field == null){ field = ''; }
		return field;
	}
	
	gallery_copy = gallery;
	
	while((matchArray = field_regex.exec(gallery_copy)) != null) {
		replacement = field(matchArray[0]);
		gallery_copy = gallery_copy.replace(matchArray[0], replacement);
	}
	
	$('#gallery').fadeOut('fast', function(){ $('#gallery').html(gallery_copy); }).delay(0).hide(0, function(){ reset(); }).fadeIn('fast');
	
	// $('#gallery').html(gallery_copy);
}

$(document).ready(function(){
	if($(document).has('#gallery').length){
		$('#gallery').hide();
	 	var gallery = true;
	}
	
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