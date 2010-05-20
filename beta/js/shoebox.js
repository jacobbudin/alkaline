var count;
var now;
var photo_files;
var photo_count;
var photo_ids;
var progress;
var progress_step;
var task = "add-photos";

function JSClock(){
	var time = new Date();
	var hour = time.getHours();
	var minute = time.getMinutes();
	var second = time.getSeconds();
	var temp = "" + ((hour > 12) ? hour - 12 : hour);
	if(hour == 0){
		temp = "12";
	}
	temp += ((minute < 10) ? ":0" : ":") + minute;
	temp += ((second < 10) ? ":0" : ":") + second;
	temp += (hour >= 12) ? " P.M." : " A.M.";
	return temp;
}

now = JSClock();

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
	photo_ids = $("#photo_ids").val();
	photo_ids += photo.id + ',';
	$("#photo_ids").attr("value", photo_ids);
	$("#photos").append('<div id="photo-' + photo.id + '" class="id"><hr /><div class="span-3 center"><img src="' + BASE + PHOTOS + photo.id + '_sq.' + photo.ext + '" alt="" class="admin_thumb" /></div><div class="span-14 last"><p class="title"><input type="text" name="photo-' + photo.id + '-title" /></p><p class="description"><textarea name="photo-' + photo.id + '-description"></textarea></p><p class="tags"><img src="' + BASE + IMAGES + 'icons/tag.png" alt="" title="Tags" /><input type="text" id="photo-' + photo.id + '-tags" /></p><p class="publish"><img src="' + BASE + IMAGES + 'icons/publish.png" alt="" title="Publish date" /><input type="text" id="photo-' + photo.id + '-published" value="' + now + '" /></p><p class="geo"><img src="' + BASE + IMAGES + 'icons/geo.png" alt="" title="Geolocation" /><input type="text" id="photo-' + photo.id + '-geo" /></p><p class="delete"><a href="#" class="delete"><img src="' + BASE + IMAGES + 'icons/delete.png" alt="" title="Delete photo" /></a></p></div></div>');
}

function updateProgress(){
	progress += progress_step;
	progress_int = parseInt(progress);
	$("#progress").progressbar({ value: progress_int });
	if(progress == 100){
		$("#progress").slideUp(1000);
		$("#add").delay(1000).removeAttr("disabled");
	}
}

function checkCount(){
	if(count == 0){
		$('#add').attr('disabled', 'disabled');
	}
}

$("#add").attr("disabled", "disabled");
$("#progress").progressbar({ value: 0 });

$.ajax({
	url: BASE + ADMIN + "tasks/" + task + ".php",
	cache: false,
	error: function(data){ alert(data); },
	dataType: "json",
	success: function(data){ photoArray(data); }
});

$(document).ready(function(){
	count = $('#count').text();
	checkCount();
	
	$("a.delete").click(function(){
		if(window.confirm('Are you sure you want to delete this photo? This action cannot be undone.')){
			photo_id = $(this).closest('div.id').attr('id');
			photo_id = /[0-9]+/.exec(photo_id);
			photo_id = photo_id[0];
			$.post(BASE + ADMIN + "tasks/delete-photo.php", { photo_id: photo_id }, function(){
				$('#photo-' + photo_id + '.id').hide('slow');
				$('#count').text(--count);
				checkCount();
			} );
		}
		event.preventDefault();
	});
});