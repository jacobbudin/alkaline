var src;
var photo;
var photo_first;
var photo_last;

function nextPhoto(){
	photo = photo.next();
	if(photo.length == 0){
		photo = photo_first;
	}
	src = photo.children('img').attr('src');
	$(document.body).css('background-image', 'url(' + src + ')');
	event.preventDefault();
}

function prevPhoto(){
	photo = photo.prev();
	if(photo.length == 0){
		photo = photo_last;
	}
	src = photo.children('img').attr('src');
	$(document.body).css('background-image', 'url(' + src + ')');
	event.preventDefault();
}

$(document).ready(function(){
	photo_first = $('ul#superview li').first();
	photo_last = $('ul#superview li').last();
	photo = photo_first;
	src = photo.children('img').attr('src');
	
	$(document.body).css('background-image', 'url(' + src + ')');
	
	$('a.control').fadeTo('slow', 0);
	
	$('a.control').hover(
		function(){
			$(this).fadeTo('slow', 1);
		},
		function(){
			$(this).fadeTo('slow', 0);
		}
	);
	
	$('a#next').click(function(){
		nextPhoto();
	});
	
	$('a#prev').click(function(){
		prevPhoto();
	});
	
	$(document).keydown(function(event){
		if(event.keyCode == '37'){
			nextPhoto();
		}
		if(event.keyCode == '38'){
			
		}
		if(event.keyCode == '39'){
			prevPhoto();
		}
		if(event.keyCode == '40'){
			
		}
	});
});