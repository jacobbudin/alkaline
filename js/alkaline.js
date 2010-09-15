var BASE = 'http://beta.alkalineapp.com/';

var ADMIN = 'admin/';
var IMAGES = 'images/';
var PHOTOS = 'photos/';

$(document).ready(function(){
	Galleria.loadTheme(BASE + 'js/jquery/themes/classic/galleria.classic.js');
	$('#gallery').galleria();
});