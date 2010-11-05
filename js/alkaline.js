var BASE = $('meta[name="base"]').attr('content');
var FOLDER_PREFIX = $('meta[name="folder_prefix"]').attr('content');

var ADMIN = FOLDER_PREFIX + 'admin/';
var IMAGES = FOLDER_PREFIX + 'images/';
var PHOTOS = FOLDER_PREFIX + 'photos/';

var slideshow;
var slideshow_photo;
var slideshow_photo_prev;
var slideshow_photo_count;
var slideshow_working = 0;
var slideshow_play = 1;

function slideshow_first(){
	slideshow = $('ul#slideshow');
	slideshow_photos = slideshow.children('li');
	slideshow_photo = slideshow_photos.first('li');
	slideshow_update();
}

function slideshow_last(){
	slideshow = $('ul#slideshow');
	slideshow_photos = slideshow.children('li');
	slideshow_photo = slideshow_photos.last('li');
	slideshow_update();
}

function slideshow_next(){
	slideshow_photo = slideshow_photo.next();
	if(slideshow_photo.length == 0){
		slideshow_first();
		slideshow_photo_prev = slideshow_photo;
	}
	else{
		slideshow_update();
	}
}

function slideshow_prev(){
	slideshow_photo = slideshow_photo.prev();
	if(slideshow_photo.length == 0){
		slideshow_last();
		slideshow_photo_prev = slideshow_photo;
	}
	else{
		slideshow_update();
	}
}

function reset(){
	photo = $('ul#slideshow').find('img');
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
	$('ul#slideshow').css('padding-top', padding + 'px');
}

function slideshow_update(){
	if(slideshow_working == 0){
		slideshow_working = 1;
		
		uncomment(slideshow_photo);
		
		$('ul#slideshow').fadeOut(100, function(){ slideshow_photo_prev.hide(); }).delay(0).hide(10, function(){ slideshow_photo.show(); reset(); }).fadeIn(100, function(){ slideshow_photo_prev = slideshow_photo; slideshow_working = 0; });
	}
}

function uncomment(slideshow_photo){
	slideshow_photo_html = slideshow_photo.html();
	slideshow_photo_html = slideshow_photo_html.replace(/^\<\!-- /gi, '');
	slideshow_photo_html = slideshow_photo_html.replace(/ --\>$/gi, '');
	slideshow_photo.html(slideshow_photo_html);
}

$(document).ready(function(){
	if($(document).has('ul#slideshow').length){
		$('ul#slideshow').hide();
		
		slideshow = $('ul#slideshow');
		slideshow_photos = slideshow.children('li');
		slideshow_photo = slideshow_photos.first('li');
		slideshow_photo_prev = slideshow_photo;
		slideshow_update();
		
		$(document).keydown(function(event){
			if(event.keyCode == '37'){
				slideshow_prev();
			}
			if(event.keyCode == '39'){
				slideshow_next();
			}
		});
	}
	
	$(window).resize(function () { reset(); });
});

$(window).load(function(){
	reset();
});