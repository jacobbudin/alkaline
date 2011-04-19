/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

var BASE = $('meta[name="base"]').attr('content');
var FOLDER_PREFIX = $('meta[name="folder_prefix"]').attr('content');
var PERMISSIONS = $('meta[name="permissions"]').attr('content');

var ADMIN = FOLDER_PREFIX + 'admin/';
var IMAGES = FOLDER_PREFIX + 'images/';
var JS = FOLDER_PREFIX + 'js/';
var WATERMARKS = FOLDER_PREFIX + 'watermarks/';

$.jQTouch({
    icon: BASE + ADMIN + 'mobile/apple-touch-icon.png',
    statusBar: 'black-translucent',
    preloadImages: [
        BASE + ADMIN + 'css/jqtouch/img/chevron_white.png',
        BASE + ADMIN + 'css/jqtouch/img/bg_row_select.gif',
        BASE + ADMIN + 'css/jqtouch/img/back_button_clicked.png',
        BASE + ADMIN + 'css/jqtouch/img/button_clicked.png'
        ]
});

$(document).ready(function(){
	$('.stats').sparkline('html', {type: 'bar', barColor: '#1aa8e0'});
	
	$('.post').click(function(){
		$(this).addClass('loading');
		id = $(this).attr('title');
		$.get('posts.php', { post_id: id}, function(data) {
			data = $.evalJSON(data);
			for(var prop in data){
				$('input[name="' + prop + '"]').val(data[prop]);
				$('textarea[name="' + prop + '"]').val(data[prop]);
				$(this).removeClass('loading');
			}
		});
	});
});