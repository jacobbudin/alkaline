var BASE = 'http://beta.alkalineapp.com/';
var ADMIN = 'admin/';
var PHOTOS = 'photos/';

$(document).ready(function(){
	$("#view a").click(function(){
		type = $(this).attr("id");
		$.post(BASE + ADMIN + "tasks/switch-view.php", { type: type }, function(data){ window.location.href = window.location.href; } );
		event.preventDefault();
	});
});