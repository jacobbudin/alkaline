var photo_ids;
var photo_count;
var progress;
var progress_step;
var task;

function photoArray(data){
	photo_ids = data;
	photo_count = photo_ids.length;
	progress = 0;
	progress_step = 100 / photo_ids.length;
	for(photo_id in photo_ids){
		$.post(BASE + ADMIN + "tasks/" + task + ".php", { photo_id: photo_ids[photo_id] }, function(data){ updateProgress(); } );
	}
}

function updateProgress(){
	progress += progress_step;
	progress_int = parseInt(progress);
	$("#progress").progressbar({ value: progress_int });
	if(progress == 100){
		$.post(BASE + ADMIN + "tasks/add-notification.php", { message: "Your photo library&#8217;s thumbnails have been rebuilt.", type: "success" }, function(data){ } );
	}
}

$(document).ready(function(){
	task = $(this).attr("id");
	$("#tasks").slideUp(500);
	$("#progress").delay(500).slideDown(500);
	$("#progress").progressbar({ value: 0 });
	$.ajax({
		url: BASE + ADMIN + "tasks/" + task + ".php",
		cache: false,
		error: function(data){ alert(data); },
		dataType: "json",
		success: function(data){ photoArray(data); }
	});
});