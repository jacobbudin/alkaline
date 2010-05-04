var photo_files;
var photo_count;
var progress;
var progress_step;
var task = "add-photos";

function photoArray(data){
	photo_files = data;
	photo_count = photo_files.length;
	progress = 0;
	progress_step = 100 / photo_files.length;
	for(photo_file in photo_files){
		$.post(BASE + ADMIN + "tasks/" + task + ".php", { photo_file: photo_files[photo_file] }, function(data){ updatePage(data); updateProgress(); } );
	}
}

function updatePage(id){
	$("#photos").append('<p id="photo-' + id + '">THIS APPEARS EACH PHOTO#' + id + '</p>');
	$("#photo-" + id).hide();
}

function updateProgress(){
	progress += progress_step;
	progress_int = parseInt(progress);
	$("#progress").progressbar({ value: progress_int });
	if(progress == 100){
		$("#progress").slideUp(500);
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