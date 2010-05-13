$(document).ready(function(){
	$("a.photo").toggle(function(){
		$("#photos").find("img").removeClass("selected");
		$(".blurb").hide();
		photo = $(this).attr("id");
		$(this).children("img").addClass("selected");
		$("#" + photo + "-blurb").show();
		event.preventDefault();
	}, function(){
		photo = $(this).attr("id");
		$(this).children("img").removeClass("selected");
		$("#" + photo + "-blurb").hide();
		event.preventDefault();
	});
});