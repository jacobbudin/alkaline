/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

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
	if(slideshow_working == 1){ return; }
	slideshow_photo = slideshow_photos.first('li');
	slideshow_update();
}

function slideshow_last(){
	if(slideshow_working == 1){ return; }
	slideshow_photo = slideshow_photos.last('li');
	slideshow_update();
}

function slideshow_next(){
	if(slideshow_working == 1){ return; }
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
	if(slideshow_working == 1){ return; }
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
	if(slideshow_photo){
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
}

function slideshow_update(){
	slideshow_working = 1;
	
	slideshow.fadeOut(100, function(){ uncomment(slideshow_photo); $('ul#slideshow li').hide(); slideshow_photo.show(); }).delay(0).hide(100, function(){ reset(); }).fadeIn(100, function(){ slideshow_photo_prev = slideshow_photo; slideshow_photo_next = slideshow_photo.next(); slideshow_photo_next.hide(0, function(){ uncomment(slideshow_photo_next); } ); slideshow_working = 0; });
}

function slideshow_play_now(){
	if(slideshow_play == 1){
		setTimeout("slideshow_play_next()", 3000);
	}
}

function slideshow_play_next(){
	if(slideshow_play == 1){
		slideshow_play_now();
		slideshow_next();
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
			
			if(event.keyCode == '80'){
				slideshow_pause();
			}
		});
	}
	
	$('#load_map').click(function() {
		map = $('#map').html();
		if(map == ''){
			thisisit = $('#load_map').attr('title');
			$('#map').hide();
			$('#map').html('<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' + thisisit + '&amp;ie=UTF8&amp;&amp;output=embed"></iframe>');
			$('#map iframe').css('border', '#fff 5px solid');
			$('#map').slideDown();
		}
		else{
			$('#map').slideToggle();
		}
		event.preventDefault();
	});
	
	$(window).resize(function () { reset(); });
	
	// PRIMARY - COLORKEY
	
	$("div.colorkey_data").each(function(){
		colors = $(this).children('.colors').text();
		colors = $.evalJSON(colors);
	
		percents = $(this).children('.percents').text();
		percents = $.evalJSON(percents);
	
		canvas = $(this).siblings('canvas');
		canvas_width = canvas.attr("width");
		canvas_height = canvas.attr("height");
		canvas_var = canvas.get(0);
	
		context = canvas_var.getContext("2d");
	
		x_pos = 0;
	
		for (var i = 0; i < colors.length; i++) {
			context.fillStyle = "rgb(" + colors[i] + ")";
			width = parseInt((percents[i] * canvas_width) / 100);
			if(i == (colors.length - 1)){
				width += 1000;
			}
			context.fillRect(x_pos, 0, width, canvas_height);
			x_pos += width;
		}
	});
});

$(window).load(function(){
	reset();
});

$(window).scroll(function() {
    $('#header_home').css('top', "-" + $(this).scrollTop() + "px");
});