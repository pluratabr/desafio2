!function($) {
    "use strict";
    var MorrisCharts = function() {};
    
    //creates line chart
    MorrisCharts.prototype.createLineChart = function(element, data, xkey, ykeys, labels, opacity, Pfillcolor, Pstockcolor, lineColors) {
        Morris.Line({
          element: element,
          data: data,
          xkey: xkey,
          ykeys: ykeys,
          labels: labels,
          fillOpacity: opacity,
          pointFillColors: Pfillcolor,
          pointStrokeColors: Pstockcolor,
          behaveLikeLine: true,
          gridLineColor: '#eee',
          hideHover: 'auto',
          lineWidth: '3px',
          pointSize: 0,
          preUnits: '$ ',
          resize: true, //defaulted to true
          lineColors: lineColors
        });
    },
    //creates area chart
    MorrisCharts.prototype.createAreaChart = function(element, pointSize, lineWidth, data, xkey, ykeys, labels, lineColors) {
        Morris.Area({
            element: element,
            pointSize: 0,
            lineWidth: 0,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            hideHover: 'auto',
            resize: true,
            gridLineColor: '#eee',
            lineColors: lineColors
        });
    },
    MorrisCharts.prototype.createDonutChart = function(element, data, colors) {
        Morris.Donut({
            element: element,
            data: data,
            resize: true, //defaulted to true
            colors: colors
        });
    },
    MorrisCharts.prototype.init = function() {
        //create line chart
        var $data  = [
            { y: '2012', a: 50, b: 0 },
            { y: '2013', a: 75, b: 50 },
            { y: '2014', a: 30, b: 80 },
            { y: '2015', a: 50, b: 50 },
            { y: '2016', a: 75, b: 10 },
            { y: '2017', a: 50, b: 40 },
            { y: '2018', a: 75, b: 50 },
            { y: '2019', a: 100, b: 70 }
          ];
        this.createLineChart('morris-line-example', $data, 'y', ['a', 'b'], ['Bitcoin', 'Ethereum'],['0.1'],['#ffffff'],['#999999'], ['#47169d', '#949cc7']);
        //creating area chart
        var $areaData = [
            { y: '2013', a: 10, b: 20 },
            { y: '2014', a: 75,  b: 65 },
            { y: '2015', a: 50,  b: 40 },
            { y: '2016', a: 75,  b: 65 },
            { y: '2017', a: 50,  b: 40 },
            { y: '2018', a: 75,  b: 65 },
            { y: '2019', a: 90, b: 60 }
        ];
        this.createAreaChart('morris-area-example', 0, 0, $areaData, 'y', ['a', 'b'], ['Bitcoin', 'Ethereum'], ['#5b69bc', "#47169d"]);
        var $donutData = [
            {label: "Download Sales", value: 12},
            {label: "In-Store Sales", value: 30},
            {label: "Mail-Order Sales", value: 20}
        ];
    this.createDonutChart('morris-donut-example', $donutData, ['#10c469', '#5b69bc', "#47169d"]);

    },
    
    //init
    $.MorrisCharts = new MorrisCharts, $.MorrisCharts.Constructor = MorrisCharts
}(window.jQuery),



//initializing
function($) {
    "use strict";
    Morris.Bar({
        element: 'morris-bar-chart',
        data: [{
            y: '2016',
            a: 100,
            b: 90,
        }, {
            y: '2017',
            a: 75,
            b: 65,
        }, {
            y: '2018',
            a: 50,
            b: 40,
        }, {
            y: '2019',
            a: 75,
            b: 65,
        }, {
            y: '2020',
            a: 50,
            b: 40,
        }, {
            y: '2021',
            a: 75,
            b: 65,
        }, {
            y: '2022',
            a: 100,
            b: 90,
        }],
        xkey: 'y',
        ykeys: ['a', 'b', 'c'],
        labels: ['A', 'B', 'C'],
        barColors: ['#47169d', '#be29ec'],
        hideHover: 'auto',
        gridLineColor: 'transparent',
        resize: true
    });
    $.MorrisCharts.init();
}(window.jQuery);