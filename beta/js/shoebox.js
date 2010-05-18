var photo_files;
var photo_count;
var photo_ids;
var progress;
var progress_step;
var task = "add-photos";

function photoArray(data){
	photo_files = data;
	photo_count = photo_files.length;
	progress = 0;
	progress_step = 100 / photo_files.length;
	for(photo_file in photo_files){
		$.post(BASE + ADMIN + "tasks/" + task + ".php", { photo_file: photo_files[photo_file] }, function(data){ appendPhoto(data); updateProgress(); } );
	}
}

function appendPhoto(photo){
	var photo = jQuery.parseJSON(photo);
	$("#photos").append('<p id="photo-' + photo.id + '"><div class="span-3 center"><img src="' + BASE + PHOTOS + photo.id + '_sq.' + photo.ext + '" alt="" class="admin_thumb" /></div><div class="span-14 last"><p><strong>Title:</strong><br /><input type="text" name="photo-' + photo.id + '-title" /></p><p><strong>Description:</strong><br /><textarea name="photo-' + photo.id + '-description"></textarea></p><p><strong>Tags:</strong><br /><input type="text" name="photo-' + photo.id + '-tags" /></p></div></p>');
	photo_ids = $("#photo_ids").attr("value");
	photo_ids += photo.id + ',';
	$("#photo_ids").attr("value", photo_ids);
	$("#photo-" + photo.id).hide(0).slideDown(1000);
}

function updateProgress(){
	progress += progress_step;
	progress_int = parseInt(progress);
	$("#progress").progressbar({ value: progress_int });
	if(progress == 100){
		$("#progress").slideUp(1000);
		$("#add").delay(1000).removeAttr("disabled");
	}
	else{
		$("#photos").append('<hr />');
	}
}

$(document).ready(function(){
	$("#progress").progressbar({ value: 0 });
	$.ajax({
		url: BASE + ADMIN + "tasks/" + task + ".php",
		cache: false,
		error: function(data){ alert(data); },
		dataType: "json",
		success: function(data){ photoArray(data); }
	});
});