var height;
var width;
var margin;
var padding;
var photo;
var photo_first;
var photo_last;

function nextPhoto(){
	resetImgDim();
	photo.removeClass('selected');
	photo = photo.next();
	if(photo.length == 0){
		photo = photo_first;
	}
	photo.addClass('selected');
	imgDim();
	event.preventDefault();
}

function prevPhoto(){
	resetImgDim();
	photo.removeClass('selected');
	photo = photo.prev();
	if(photo.length == 0){
		photo = photo_last;
	}
	photo.addClass('selected');
	imgDim();
	event.preventDefault();
}

function imgDim(){
	height = photo.innerHeight();
	width = photo.innerWidth();
	margin = (670 / 2) - (height / 2) - 30;
	photo.children('img').css('margin-top', margin + 'px');
	padding = '305px';
	$('#controls #next').css('padding', padding + ' inherit');
	$('#controls #prev').css('padding', padding + ' inherit');
}

function resetImgDim(){
	photo.children('img').css('margin-top', '0');
}

$(document).ready(function(){
	photo_first = $('ul#slides li').first();
	photo_last = $('ul#slides li').last();
	photo = photo_first;
	
	photo.addClass('selected');
	
	$('#controls a').fadeTo('slow', 0);
	
	$('#controls a').hover(
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
		if(event.keyCode == '39'){
			prevPhoto();
		}
	});
});

$(window).load(function(){
	imgDim();
});