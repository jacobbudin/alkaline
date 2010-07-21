var BASE = 'http://beta.alkalineapp.com/';

var ADMIN = 'admin/';
var IMAGES = 'images/';
var PHOTOS = 'photos/';

$(document).ready(function(){
	var statistics_views = $("#statistics_views").attr("title");
	statistics_views = jQuery.parseJSON(statistics_views);
	
	var statistics_visitors = $("#statistics_visitors").attr("title");
	statistics_visitors = jQuery.parseJSON(statistics_visitors);
	
	$.plot($("#statistics_holder"),[{
		label: "Page views",
		data: statistics_views,
		bars: { show: true, lineWidth: 18 },
		yaxis: 1
	},
	{
		label: "Unique visitors",
		data: statistics_visitors,
		bars: { show: true, lineWidth: 18 },
		yaxis: 1
	}],{
		legend: { show: true, backgroundOpacity: 0, labelBoxBorderColor: "#333", position: "ne", margin: 5 },
		colors: ["#0096db", "#8dc9e8"],
		xaxis: { mode: "time", tickLength: 0, autoscaleMargin: 0 },
		yaxis: { tickDecimals: 0 },
		grid: { color: "#ccc", borderColor: "#333", tickColor: "#333", labelMargin: 10, hoverable: true, autoHighlight: true }
	});
	
	$("#statistics_holder").bind("plothover", function(event, pos, item){
		if(item){
			highlight(item.series, item.datapoint);
			alert("You clicked a point!");
		}
	});

	
	$(".tickLabels").css('font-size', '');
	
	$("#view a").click(function(){
		type = $(this).attr("id");
		$.post(BASE + ADMIN + "tasks/switch-view.php", { type: type }, function(data){ window.location.href = window.location.href; } );
		event.preventDefault();
	});
});