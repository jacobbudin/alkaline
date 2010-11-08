var BASE = $('meta[name="base"]').attr('content');
var FOLDER_PREFIX = $('meta[name="folder_prefix"]').attr('content');

var ADMIN = FOLDER_PREFIX + 'admin/';
var IMAGES = FOLDER_PREFIX + 'images/';
var PHOTOS = FOLDER_PREFIX + 'photos/';

var slideshow;
var slideshow_photo;
var slideshow_photo_prev;
var slideshow_working = 0;
var slideshow_play = 1;

function slideshow_first(){
	slideshow_photo = slideshow_photos.first('li');
	slideshow_update();
}

function slideshow_last(){
	slideshow_photo = slideshow_photos.last('li');
	slideshow_update();
}

function slideshow_next(){
	slideshow_photo_next = slideshow_photo.next();
	if(slideshow_photo_next.length == 0){
		slideshow_first();
	}
	else{
		slideshow_photo = slideshow_photo_next;
		slideshow_update();
	}
}

function slideshow_prev(){
	slideshow_photo_next = slideshow_photo.prev();
	if(slideshow_photo_next.length == 0){
		slideshow_last();
	}
	else{
		slideshow_photo = slideshow_photo_next;
		slideshow_update();
	}
}

function reset(){
	photo = slideshow_photo.find('img');
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
	slideshow_photo.css('padding-top', padding + 'px');
}

function slideshow_update(){
	if(slideshow_working == 0){
		slideshow_working = 1;
		
		slideshow.fadeOut(100, function(){ uncomment(slideshow_photo); slideshow_photo_prev.hide(); slideshow_photo.show(); }).delay(0).hide(100, function(){ reset(); }).fadeIn(100, function(){ slideshow_photo_prev = slideshow_photo; slideshow_photo_next = slideshow_photo.next(); slideshow_photo_next.hide(0, function(){ uncomment(slideshow_photo_next); } ); slideshow_working = 0; });
	}
}

function slideshow_play_now(){
	if(slideshow_play == 1){
		setTimeout("slideshow_play_next()", 3000);
	}
}

function slideshow_play_next(){
	if(slideshow_play == 1){
		slideshow_next();
		slideshow_play_now();
	}
}

function slideshow_pause(){
	if(slideshow_play == 1){
		slideshow_play = 0;
	}
	else{
		slideshow_play = 1;
		slideshow_play_next();
	}
}

function slideshow_play(){
	slideshow_play = 1;
	slideshow_play_next();
}

function slideshow_stop(){
	slideshow_play = 0;
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
		slideshow_play_now();
		
		$('#slideshow_pause').click(function() {
			slideshow_pause();
		});
				
		$('#slideshow_play').click(function() {
			slideshow_play();
		});
		
		$('#slideshow_stop').click(function() {
			slideshow_stop();
		});
		
		$(document).keydown(function(event){
			if(event.keyCode == '38'){
				slideshow_play = 0;
				slideshow_first();
			}
			if(event.keyCode == '37'){
				slideshow_play = 0;
				slideshow_prev();
			}
			if(event.keyCode == '40'){
				slideshow_play = 0;
				slideshow_last();
			}
			if(event.keyCode == '39'){
				slideshow_play = 0;
				slideshow_next();
			}
		});
	}
	
	$(window).resize(function () { reset(); });
});

$(window).load(function(){
	reset();
});