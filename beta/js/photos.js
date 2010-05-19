var photo_id;

function photoSelect(photo){
	$(".photo").children("img").removeClass("selected");
	$(".blurb").slideUp();
	if(typeof(photo) == 'object'){
		if(photo_id != photo.attr("id")){
			photo_id = photo.attr("id");
			photo.children("img").addClass("selected");
			position = photo.position();
			position_top = String(position.top + 11);
			background_position = String(-960 + position.left);
			$("#" + photo_id + "-blurb").css("top", position_top + "px");
			$("#" + photo_id + "-blurb").css("background-position", background_position + "px 0");					
			$("#" + photo_id + "-blurb").slideDown();
			window.setTimeout(scroll, 400);
		}
		else{
			photo_id = null;
		}
	}
	else{
		photo_id = null;
	}
}

function scroll(){	
	$(document).scrollTop(position_top+400);
}

$(document).ready(function(){
	$("a.photo").click(function(){
		photoSelect($(this));
		event.preventDefault();
	});
	$(document).keydown(function(event){
		if(event.keyCode == '27'){
			photoSelect(0);
			event.preventDefault();
		}
	});
	save = $(".save").click(function(){
		$(this).attr("disabled", "disabled");
		photo_id = $(this).siblings(".photo_ids").val();
		photo_title = $("#photo-" + photo_id + "-title").val();
		photo_description = $("#photo-" + photo_id + "-description").val();
		$.post(BASE + ADMIN + "tasks/save-changes.php", { photo_id: photo_id, photo_title: photo_title, photo_description: photo_description }, function(data){ save.removeAttr("disabled"); photoSelect(0); } );
		event.preventDefault();
	});
});