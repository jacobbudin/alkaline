var photo_id;

$(document).ready(function(){
	$("a.photo").click(function(){
		$(".photo").children("img").removeClass("selected");
		$(".blurb").hide();
		if(photo_id != $(this).attr("id")){
			photo_id = $(this).attr("id");
			$(this).children("img").addClass("selected");
			$("#" + photo_id + "-blurb").show();
		}
		else{
			photo_id = null;
		}
		event.preventDefault();
	});
});