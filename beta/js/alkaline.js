var BASE = 'http://beta.alkalineapstats.com/';

var ADMIN = 'admin/';
var IMAGES = 'images/';
var PHOTOS = 'photos/';

$(document).ready(function(){
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
		legend: { show: true, backgroundOpacity: 0, labelBoxBorderColor: "#333", position: "ne", margin: 5 },
		colors: ["#0096db", "#8dc9e8"],
		xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0 },
		yaxis: { tickDecimals: 0 },
		grid: { color: "#777", borderColor: "#333", tickColor: "#333", labelMargin: 10, hoverable: true, autoHighlight: true }
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
			$(this).text('Today');
		}
	});
	
	$(".tickLabels").css('font-size', '');
	
	$("#view a").click(function(){
		type = $(this).attr("id");
		$.post(BASE + ADMIN + "tasks/switch-view.php", { type: type }, function(data){ window.location.href = window.location.href; } );
		event.preventDefault();
	});
});