var photo_id;

$(document).ready(function(){
	$("a.photo").click(function(){
		$(".photo").children("img").removeClass("selected");
		$(".blurb").slideUp();
		if(photo_id != $(this).attr("id")){
			photo_id = $(this).attr("id");
			$(this).children("img").addClass("selected");
			position = $(this).position();
			position_top = String(position.top + 11);
			background_position = String(-965 + position.left);
			$("#" + photo_id + "-blurb").css("top", position_top + "px");
			$("#" + photo_id + "-blurb").css("background-position", background_position + "px 0");
			$("#" + photo_id + "-blurb").slideDown();
		}
		else{
			photo_id = null;
		}
		event.preventDefault();
	});
});