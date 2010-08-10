var BASE = 'http://beta.alkalineapp.com/';

var ADMIN = 'admin/';
var IMAGES = 'images/';
var PHOTOS = 'photos/';

var task;

// SHOEBOX

function now(){
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

var now = now();

function photoArray(photo_files){
	photo_count = photo_files.length;
	progress = 0;
	progress_step = 100 / photo_files.length;
	for(photo_file in photo_files){
		$.post(BASE + ADMIN + "tasks/" + task + ".php", { photo_file: photo_files[photo_file] }, function(data){ appendPhoto(data); updateProgress(); } );
	}
}

function appendPhoto(photo){
	var photo = jQuery.parseJSON(photo);
	photo_ids = $("#shoebox_photo_ids").val();
	photo_ids += photo.id + ',';
	$("#shoebox_photo_ids").attr("value", photo_ids);
	$("#shoebox_photos").append('<div id="photo-' + photo.id + '" class="id"><hr /><div class="span-3 center"><img src="' + BASE + PHOTOS + photo.id + '_sq.' + photo.ext + '" alt="" class="admin_thumb" /></div><div class="span-14 last"><p class="title"><input type="text" name="photo-' + photo.id + '-title" /></p><p class="description"><textarea name="photo-' + photo.id + '-description"></textarea></p><p class="tags"><img src="' + BASE + IMAGES + 'icons/tag.png" alt="" title="Tags" /><input type="text" id="photo-' + photo.id + '-tags" /></p><p class="publish"><img src="' + BASE + IMAGES + 'icons/publish.png" alt="" title="Publish date" /><input type="text" id="photo-' + photo.id + '-published" value="' + now + '" /></p><p class="geo"><img src="' + BASE + IMAGES + 'icons/geo.png" alt="" title="Geolocation" /><input type="text" id="photo-' + photo.id + '-geo" /></p><p class="delete"><a href="#" class="delete"><img src="' + BASE + IMAGES + 'icons/delete.png" alt="" title="Delete photo" /></a></p></div></div>');
}

function updateProgress(){
	progress += progress_step;
	progress_int = parseInt(progress);
	$("#shoebox_progress").progressbar({ value: progress_int });
	if(progress == 100){
		$("#shoebox_progress").slideUp(1000);
		$("#shoebox_add").delay(1000).removeAttr("disabled");
	}
}

function checkCount(){
	if(count == 0){
		$('#shoebox_add').attr('disabled', 'disabled');
	}
}

function executeTask(){
	$.ajax({
		url: BASE + ADMIN + "tasks/" + task + ".php",
		cache: false,
		error: function(data){ alert(data); },
		dataType: "json",
		success: function(data){ photoArray(data); }
	});
}

$(document).ready(function(){
	// PRIMARY
	var page = $("h1").first().text();
	
	// PRIMARY - SHOW/HIDE PANELS
	$(".reveal").hide();
	var original = $("a.show").text();
	var re = /Show(.*)/;
	var modified = 'Hide' + original.replace(re, "$1");
	
	$("a.show").toggle(
		function(){
			$(this).siblings(".switch").html('&#9662;');
			$(this).text(modified);
			$(this).siblings(".reveal").slideDown();
			event.preventDefault();
		},
		function(){
			$(this).siblings(".switch").html('&#9656;');
			$(this).text(original);
			$(this).siblings(".reveal").slideUp();
			event.preventDefault();
		}
	);
	
	// PRIMARY - DATEPICKER
	
	$(".date").datepicker({
		showOn: 'button',
		buttonImage: BASE + IMAGES + '/icons/calendar.png',
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		constrainInput: false,
		showAnim: null
	});
	
	// PRIMARY - NAVIGATION
	
	$("#navigation ul li").hover(
		function(){
			$(this).find("span.small").html('&#9662;');
			
		}, 
		function(){
			$(this).find("span.small").html('&#9656;');
		}
	);
	
	$("#navigation ul li ul").hover(
		function(){
			$(this).siblings('a').addClass('selected');
			$(this).parent('li').mouseenter(handlerIn);
			
		}, 
		function(){
			$(this).siblings('a').removeClass('selected');
			$(this).parent('li').mouseleave(handlerOut);
		}
	);
	
	// SHOEBOX
	
	if(page == 'Shoebox'){
		task = "add-photos";
		executeTask();
	
		$("#shoebox_add").attr("disabled", "disabled");
		$("#shoebox_progress").progressbar({ value: 0 });
	
		count = $('#count').text();
		checkCount();
	}
	
	// PAGE (EDIT)
	
	if(page == 'Page'){
		var markup_row = $("#tr_page_markup");
		var markup = markup_row.find("select").attr("title");
		
		if(markup){
			markup_row.find("input").attr("checked", "checked");
			markup_row.find("option[value='" + markup + "']").attr("selected", "selected");
		}
	}
	
	// DASHBOARD
	
	if(page == 'Dashboard'){
		var statistics_views = $("#statistics_views").attr("title");
		statistics_views = jQuery.parseJSON(statistics_views);
	
		var statistics_visitors = $("#statistics_visitors").attr("title");
		statistics_visitors = jQuery.parseJSON(statistics_visitors);
	
		var stats = $.plot($("#statistics_holder"),[{
			label: "Page views",
			data: statistics_views,
			bars: { show: true, lineWidth: 18 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: statistics_visitors,
			bars: { show: true, lineWidth: 18 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: true, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 10 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0 },
			yaxis: { tickDecimals: 0 },
			grid: { color: "#777", borderColor: "#ccc", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
	
		$.each(stats.getData()[0].data, function(i, el){
			var o = stats.pointOffset({x: el[0], y: el[1]});
			if(el[1] > 0){
			  $('<div class="point">' + el[1] + '</div>').css( {
			    position: 'absolute',
			    left: o.left - 12,
			    top: o.top - 20,
			  }).appendTo(stats.getPlaceholder());
			}
		});
	
		var time = new Date();
		var month = time.getMonth();
	
		if(month == 0){ month = 'Jan'; }
		if(month == 1){ month = 'Feb'; }
		if(month == 2){ month = 'Mar'; }
		if(month == 3){ month = 'Apr'; }
		if(month == 4){ month = 'May'; }
		if(month == 5){ month = 'Jun'; }
		if(month == 6){ month = 'Jul'; }
		if(month == 7){ month = 'Aug'; }
		if(month == 8){ month = 'Sep'; }
		if(month == 9){ month = 'Oct'; }
		if(month == 10){ month = 'Nov'; }
		if(month == 11){ month = 'Dec'; }

		var day = time.getDate();
	
		$(".tickLabel").each(function(index){
			var text = $(this).text();
			if(text == (month + ' ' + day)){
				$(this).text('Today').css('color', '#000');
			}
		});
	
		$(".tickLabels").css('font-size', '');
	}
	
	if(page == 'Statistics'){
		var h_statistics_views = $("#h_views").attr("title");
		h_statistics_views = jQuery.parseJSON(h_statistics_views);
	
		var h_statistics_visitors = $("#h_visitors").attr("title");
		h_statistics_visitors = jQuery.parseJSON(h_statistics_visitors);
	
		var h_stats = $.plot($("#h_holder"),[{
			label: "Page views",
			data: h_statistics_views,
			bars: { show: true, lineWidth: 7 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: h_statistics_visitors,
			bars: { show: true, lineWidth: 7 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: false, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 10 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0.01, timeformat: "%h %p" },
			yaxis: { tickDecimals: 0 },
			grid: { color: "#777", borderColor: "#ccc", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
		
	
		var d_statistics_views = $("#d_views").attr("title");
		d_statistics_views = jQuery.parseJSON(d_statistics_views);
	
		var d_statistics_visitors = $("#d_visitors").attr("title");
		d_statistics_visitors = jQuery.parseJSON(d_statistics_visitors);
	
		var d_stats = $.plot($("#d_holder"),[{
			label: "Page views",
			data: d_statistics_views,
			bars: { show: true, lineWidth: 6 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: d_statistics_visitors,
			bars: { show: true, lineWidth: 6 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: false, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 10 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, minTickSize: [3, "day"] },
			yaxis: { tickDecimals: 0 },
			grid: { color: "#777", borderColor: "#ccc", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
		
		var m_statistics_views = $("#m_views").attr("title");
		m_statistics_views = jQuery.parseJSON(m_statistics_views);
	
		var m_statistics_visitors = $("#m_visitors").attr("title");
		m_statistics_visitors = jQuery.parseJSON(m_statistics_visitors);
	
		var m_stats = $.plot($("#m_holder"),[{
			label: "Page views",
			data: m_statistics_views,
			bars: { show: true, lineWidth: 13 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		},
		{
			label: "Unique visitors",
			data: m_statistics_visitors,
			bars: { show: true, lineWidth: 13 },
			shadowSize: 10,
			hoverable: true,
			yaxis: 1
		}],{
			legend: { show: false, backgroundOpacity: 0, labelBoxBorderColor: "#ddd", position: "ne", margin: 10 },
			colors: ["#0096db", "#8dc9e8"],
			xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0, minTickSize: [3, "month"] },
			yaxis: { tickDecimals: 0 },
			grid: { color: "#777", borderColor: "#ccc", tickColor: "#eee", labelMargin: 10, hoverable: true, autoHighlight: true }
		});
		
		$(".tickLabel").each(function(index){
			var text = $(this).text();
			if(text == ('12 am')){
				$(this).text('Midnight');
			}
			else if(text == ('12 pm')){
				$(this).text('Noon');
			}
		});

	}
	
	$("#view a").click(function(){
		type = $(this).attr("id");
		$.post(BASE + ADMIN + "tasks/switch-view.php", { type: type }, function(data){ window.location.href = window.location.href; } );
		event.preventDefault();
	});
});