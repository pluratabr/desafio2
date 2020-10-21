
(function($) {
    'use strict';
    $(function () {
       
 // 2nd Highlighted Card  //
 var options = {
    chart: {
        height: 170,
        type: 'radialBar',
        offsetX: -15
    },
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 225,
            hollow: {
                margin: 0,
                size: '90%',
                background: 'transparent',
                image: undefined,
                imageOffsetX: 0,
                imageOffsetY: 10,
                position: 'front',
            },
            track: {
                background: 'rgba(255,255,255,.2)',
                strokeWidth: '90%',
                margin: 0,
            },

            dataLabels: {
                showOn: 'always',
                name: {
                    show: false,
                },
                value: {
                    formatter: function (val) {
                        return parseInt(val);
                    },
                    fontFamily: 'Poppins',
                    color: '#FFF',
                    fontSize: '20px',
                    show: true,
                },
                style: {
                    colors: ['#FFF']
                }
            }
        }
    },
    fill: {
        colors: ['#FFF']
    },
    series: [75],
    stroke: {
        lineCap: 'round'
    },
    labels: ['Percent']

}

var chart = new ApexCharts(
    document.querySelector("#network-graph"),
    options
);
chart.render();
var lineCtx = document.getElementById('portal-weekly-highlights').getContext("2d");
var myChart = new Chart(lineCtx, {
    type: 'bar',
    data: {
        labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        datasets: [{
            label: "Amazon",
            data: [25, 15, 20, 50, 25, 45, 40],
            backgroundColor: '#4765FF',
            borderColor: '#4765FF',
            borderWidth: 3,
            radius: 0,
            pointStyle: 'line'
        },
            {
                label: "Wallmart",
                data: [20, 10, 35, 55, 20, 65, 45],
                backgroundColor: '#dae0ff',
                borderColor: '#dae0ff',
                borderWidth: 3,
                radius: 0,
                pointStyle: 'line'
            }]
    },
    options: {
        maintainAspectRatio: false,
        legend: {
            display: true,
            position: "top",
            labels: {
                usePointStyle: true,
                fontSize: 13,
                fontFamily: "'Poppins', sans-serif",
                fontColor: '#585e7f',
                fontStyle: '400',
            },
        },

        scales: {

            xAxes: [{
                barPercentage: 1,
                categoryPercentage: 1,
                barThickness: 10,
                maxBarThickness: 10,
                ticks: {
                    display: true,
                    beginAtZero: true,
                    fontColor: '#afb2c5',
                    fontFamily: "'Poppins'",
                    fontStyle: '400',
                    fontSize: 13,
                    padding: 10
                },
                gridLines: false
            }],
            yAxes: [{
                gridLines: {
                    drawBorder: false,
                    display: true,
                    color: '#ced1e7',
                    borderDash: [2, 5],
                    zeroLineWidth: 1,
                    zeroLineColor: '#ced1e7',
                    zeroLineBorderDash: [2, 5]
                },
                categoryPercentage: 0.5,
                ticks: {
                    display: true,
                    beginAtZero: true,
                    stepSize: 20,
                    max: 80,
                    fontColor: '#afb2c5',
                    fontFamily: "'Poppins'",
                    fontStyle: '400',
                    fontSize: 13,
                    padding: 10
                }
            }],
        },
        tooltips: {
            backgroundColor: '#4765FF',
            bodyFontFamily: 'Poppins',
            bodyFontColor: '#FFF',
            bodyFontSize: 12,
            displayColors: false,
            intersect: false,
        },
    },
    elements: {
        point: {
            radius: 0
        }
    }
});
var options = {
    series: [{
        data: [54, 65, 51, 74, 32, 53, 31]
    }, {
        data: [63, 42, 43, 62, 23, 54, 42]
    }],
    chart: {
        type: 'bar',
        height: 350,
        toolbar: {
            show: true
        }
    },
    colors: ['#5b69bc', '#47169d'],
    plotOptions: {
        bar: {
            horizontal: false,
            dataLabels: {
                position: 'top',
            },
        }
    },
    dataLabels: {
        enabled: true,
        offsetX: 0,
        style: {
            fontSize: '12px',
            colors: ['#fff']
        }
    },
    stroke: {
        show: true,
        width: 1,
        colors: ['#fff']
    },
    xaxis: {
        categories: [2013, 2014, 2015, 2016, 2017, 2018, 2019],
    },
};

var chart = new ApexCharts(
    document.querySelector("#chart2"),
    options
);

chart.render();

        ///Fifth Row JS///
    });
})(jQuery);