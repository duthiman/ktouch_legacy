/*
Template Name: Color Admin - Responsive Admin Dashboard Template build with Twitter Bootstrap 3 & 4
Version: 4.0.0
Author: Sean Ngu
Website: http://www.seantheme.com/color-admin-v4.0/admin/
*/

 

var handleInteractiveChart = function () {
	"use strict";
    function showTooltip(x, y, contents) {
        $('<div id="tooltip" class="flot-tooltip">' + contents + '</div>').css( {
            top: y - 45,
            left: x - 55
        }).appendTo("body").fadeIn(200);
    }
	if ($('#interactive-chart').length !== 0) {
	

        var data1 = [  
            [1, 40], [2, 50], [3, 60], [4, 60], [5, 60], [6, 65], [7, 75], [8, 90], [9, 100], [10, 105], 
            [11, 110], [12, 110], [13, 120], [14, 130], [15, 135],[16, 145], [17, 132], [18, 123], [19, 135], [20, 150] 
        ];
        var data2 = [
            [1, 10],  [2, 6], [3, 10], [4, 12], [5, 18], [6, 20], [7, 25], [8, 23], [9, 24], [10, 25], 
            [11, 18], [12, 30], [13, 25], [14, 25], [15, 30], [16, 27], [17, 20], [18, 18], [19, 31], [20, 23]
        ];
        var xLabel = [
            [1,'May&nbsp;14'],[2,''],[3,'May&nbsp;15'],[4,''],[5,''],[6,'May&nbsp;19'],[7,''],[8,''],[9,'May&nbsp;22'],[10,''],
            [11,''],[12,'May&nbsp;25'],[13,''],[14,''],[15,'May&nbsp;28'],[16,''],[17,''],[18,'May&nbsp;31'],[19,''],[20,'']
        ];
        $.plot($("#interactive-chart"), [{
			data: data1, 
			label: "Sales", 
			color: COLOR_BLUE,
			lines: { show: true, fill:false, lineWidth: 2 },
			points: { show: true, radius: 3, fillColor: COLOR_WHITE },
			shadowSize: 0
		}, {
			data: data2,
			label: 'Purchases',
			color: COLOR_RED,
			lines: { show: true, fill:false, lineWidth: 2 },
			points: { show: true, radius: 3, fillColor: COLOR_WHITE },
			shadowSize: 0
		}], {
			xaxis: {  ticks:xLabel, tickDecimals: 0, tickColor: COLOR_BLACK_TRANSPARENT_2 },
			yaxis: {  ticks: 10, tickColor: COLOR_BLACK_TRANSPARENT_2, min: 0, max: 200 },
			grid: { 
				hoverable: true, 
				clickable: true,
				tickColor: COLOR_BLACK_TRANSPARENT_2,
				borderWidth: 1,
				backgroundColor: 'transparent',
				borderColor: COLOR_BLACK_TRANSPARENT_2
			},
			legend: {
				labelBoxBorderColor: COLOR_BLACK_TRANSPARENT_2,
				margin: 10,
				noColumns: 1,
				show: true
			}
		});
        var previousPoint = null;
        $("#interactive-chart").bind("plothover", function (event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));
            if (item) {
                if (previousPoint !== item.dataIndex) {
                    previousPoint = item.dataIndex;
                    $("#tooltip").remove();
                    var y = item.datapoint[1].toFixed(2);
                    
                    var content = item.series.label + " " + y;
                    showTooltip(item.pageX, item.pageY, content);
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;            
            }
            event.preventDefault();
        });
    }
};

 
//     function renderDashboardSparkline() {
//         var value = [50,30,45,40,50,20,35,40,50,70,90,40];
//         options.type = 'line';
//         options.height = '23px';
//         options.lineColor = COLOR_RED;
//         options.highlightLineColor = COLOR_RED;
//         options.highlightSpotColor = COLOR_RED;
        
//         var countWidth = $('#sparkline-unique-visitor').width();
//         if (countWidth >= 200) {
//             options.width = '200px';
//         } else {
//             options.width = '100%';
//         }
        
//         $('#sparkline-unique-visitor').sparkline(value, options);
//         options.lineColor = COLOR_ORANGE;
//         options.highlightLineColor = COLOR_ORANGE;
//         options.highlightSpotColor = COLOR_ORANGE;
//         $('#sparkline-bounce-rate').sparkline(value, options);
//         options.lineColor = COLOR_GREEN;
//         options.highlightLineColor = COLOR_GREEN;
//         options.highlightSpotColor = COLOR_GREEN;
//         $('#sparkline-total-page-views').sparkline(value, options);
//         options.lineColor = COLOR_BLUE;
//         options.highlightLineColor = COLOR_BLUE;
//         options.highlightSpotColor = COLOR_BLUE;
//         $('#sparkline-avg-time-on-site').sparkline(value, options);
//         options.lineColor = COLOR_GREY;
//         options.highlightLineColor = COLOR_GREY;
//         options.highlightSpotColor = COLOR_GREY;
//         $('#sparkline-new-visits').sparkline(value, options);
//         options.lineColor = COLOR_BLACK;
//         options.highlightLineColor = COLOR_BLACK;
//         options.highlightSpotColor = COLOR_GREY;
//         $('#sparkline-return-visitors').sparkline(value, options);
//     }
    
//     renderDashboardSparkline();
    
//     $(window).on('resize', function() {
//         $('#sparkline-unique-visitor').empty();
//         $('#sparkline-bounce-rate').empty();
//         $('#sparkline-total-page-views').empty();
//         $('#sparkline-avg-time-on-site').empty();
//         $('#sparkline-new-visits').empty();
//         $('#sparkline-return-visitors').empty();
//         renderDashboardSparkline();
//     });
// };

// var handleDashboardDatepicker = function() {
// 	"use strict";
//     $('#datepicker-inline').datepicker({
//         todayHighlight: true
//     });
// };

// var handleDashboardTodolist = function() {
// 	"use strict";
//     $('[data-click=todolist]').click(function() {
//         var targetList = $(this).closest('li');
//         if ($(targetList).hasClass('active')) {
//             $(targetList).removeClass('active');
//         } else {
//             $(targetList).addClass('active');
//         }
//     });
// };
var handleInteractivePieChart = function () {
    'use strict';
    if ($('#interactive-pie-chart').length !== 0) {
        var data = [];
        var series = 10;
        //var colorArray = [COLOR_ORANGE, COLOR_BLACK_LIGHTER, COLOR_GREY];
        for( var i = 0; i<series; i++)
        {
            data[i] = { label: 'Series numbers '+(i+1), data: Math.floor(Math.random()*100)+1};
        }
        $.plot($('#interactive-pie-chart'), data, {
            series: {
                pie: { 
                    show: true
                }
            },
            grid: {
                hoverable: true,
                clickable: true
            }
        });
        $('#interactive-pie-chart').bind('plotclick', function(event, pos, obj) {
            if (!obj) {
                return;
            }
            var percent = parseFloat(obj.series.percent).toFixed(2);
            alert(obj.series.label + ': ' + percent + '%');
        });
    }
};


// var handlePieAndDonutChart = function() {
//     "use strict";
//     // set the dimensions and margins of the graph
 
//     var pieChartData = [
//         { 'label': 'One', 'value' : 29}, 
//         { 'label': 'Two', 'value' : 12}, 
//         { 'label': 'Three', 'value' : 32}, 
//         { 'label': 'Four', 'value' : 196}, 
//         { 'label': 'Five', 'value' : 17}, 
//         { 'label': 'Six', 'value' : 98}, 
//         { 'label': 'Seven', 'value' : 13}, 
//         { 'label': 'Eight', 'value' : 5}
//     ];

//     nv.addGraph(function() {
//         var pieChart = nv.models.pieChart()
//           .x(function(d) { return d.label })
//           .y(function(d) { return d.value })
//           .showLabels(false)
//           .labelThreshold(.5);

//         d3.select('#nv-pie-chart').append('svg')
//             .attr("width", 550)
//             .attr("height", 350)  
//             .datum(pieChartData)
//             .transition().duration(350)
//             .call(pieChart);

//         return pieChart;
//     });

// };

var Dashboard = function () {
	"use strict";
    return {
        //main function
        init: function () { 
            handleInteractiveChart();
            // handleDashboardSparkline();
            // handleDonutChart();
            handleInteractivePieChart();
            // handlePieAndDonutChart();
            // handleDashboardTodolist();
            // handleVectorMap();
            // handleDashboardDatepicker();
        }
    };
}();