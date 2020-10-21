(function($) {
    'use strict';
    $(function () {
        // Statistics Canvas Graph Code Start //
        var canvas = document.getElementById("canvas-stats");

        var gradientBlue = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
        gradientBlue.addColorStop(0, '#1627D3');
        gradientBlue.addColorStop(1, '#5C68E1');

        var gradientRed = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
        gradientRed.addColorStop(0, '#F95062');
        gradientRed.addColorStop(1, '#f300ff');

        var gradientGreen = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
        gradientGreen.addColorStop(0, '#17d1bd');
        gradientGreen.addColorStop(1, '#12f1bf');

        var gradientWarn = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
        gradientWarn.addColorStop(0, '#FFC868');
        gradientWarn.addColorStop(1, '#DDED3D');

        var gradientInfo = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
        gradientInfo.addColorStop(0, '#36afff');
        gradientInfo.addColorStop(1, '#8ED5FF');

        var gradientDark = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
        gradientDark.addColorStop(0, '#4E73E5');
        gradientDark.addColorStop(1, '#95abef');

        window.arcSpacing = 0.15;
        window.segmentHovered = false;

        Chart.elements.Arc.prototype.draw = function() {
            var ctx = this._chart.ctx;
            var vm = this._view;
            var sA = vm.startAngle;
            var eA = vm.endAngle;
            var chartArea = this._chart.chartArea;

            ctx.beginPath();
            ctx.arc(vm.x, vm.y, vm.outerRadius, sA + window.arcSpacing, eA - window.arcSpacing);
            ctx.strokeStyle = vm.backgroundColor;
            ctx.lineWidth = vm.borderWidth;
            ctx.lineCap = 'round';

            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            var centerX = ((chartArea.left + chartArea.right) / 2);
            var centerY = ((chartArea.top + chartArea.bottom) / 2);
            ctx.font = "40px Poppins Light";
            ctx.fillStyle = "#717789";
            var txt = "$400K";
            //Draw text in center
            ctx.fillText(txt, centerX, centerY);

            ctx.stroke();
            ctx.closePath();
        };

        var config = {
            type: 'doughnut',
            data: {
                labels: [
                    "India",
                    "China",
                    "US",
                    "UK",
                    "Australia",
                    "Canada"
                ],
                datasets: [
                    {
                        data: [400, 350, 290, 210, 320, 300],
                        backgroundColor: [
                            gradientRed,
                            gradientBlue,
                            gradientGreen,
                            gradientInfo,
                            gradientWarn,
                            gradientDark
                        ],
                    }
                ]
            },
            options: {
                responsive: false,
                cutoutPercentage: 80,
                elements: {
                    arc: {
                        borderWidth: 5,
                    },
                    elements: {
                        center: {
                            text: '33%',
                            color: '#ced1e7',
                            fontStyle: 'Poppins',
                            fontSize: '20',
                            sidePadding: 20
                        }
                    },
                },
                legend: {
                    display: false,
                },
                tooltips: {
                    backgroundColor: '#1627D3',
                    bodyFontFamily: 'Poppins',
                    bodyFontColor: '#FFF',
                    bodyFontSize: 12,
                    displayColors: false
                },
            },
        };
        window.chart = new Chart(canvas, config);
        // Statistics Canvas Graph Code End //
        //ApexChart - Product Radial Chart
        var options = {
            chart: {
                height: 325,
                type: 'radialBar',
                toolbar: {
                    show: false
                }
            },
            fill: {
                colors:['#FF5666', '#FFC555', '#00CB8E', '#4765FF']
            },
            plotOptions: {
                radialBar: {
                    dataLabels: {
                        name: {
                            fontFamily: 'Poppins',
                            fontSize: '22px',
                            color: '#585e7f'
                        },
                        value: {
                            fontFamily: 'Poppins',
                            fontSize: '16px',
                        },
                        total: {
                            show: true,
                            label: 'Sold Item',
                            formatter: function (w) {
                                return 17282;
                            }
                        },
                        style: {
                            colors: ['#FF5666', '#FFC555', '#00CB8E', '#4765FF']
                        }
                    }
                }
            },
            stroke: {
                lineCap: 'round'
            },
            series: [44, 55, 67, 83],
            labels: ['Iphone 6s', 'Moto Razr', 'Oneplus 6T', 'S10+'],

        }

        var chart = new ApexCharts(
            document.querySelector("#product-highlights"),
            options
        );

        chart.render();
            //Chart #1
    var options = {
        chart: {
            height: 340,
            type: 'area',
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        series: [{
            name: 'series1',
            data: [31, 40, 28, 51, 42, 109, 100]
        }, {
            name: 'series2',
            data: [11, 32, 45, 32, 34, 52, 41]
        }],

        xaxis: {
            type: 'datetime',
            categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00"],
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
        }
    }

    var chart = new ApexCharts(
        document.querySelector("#example-1"),
        options
    );

    chart.render();
    var chartTextColor = '#96A2B4';
    var options = {

        chart: {
            height: 350,
            type: 'bar',
            foreColor: chartTextColor,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                endingShape: 'rounded',
                columnWidth: '55%',
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        colors: ['#D24BFA', '#FA4BBD', '#6CCBF8'],
        series: [{
            name: 'Net Profit',
            data: [40, 50, 55, 60, 60, 51, 69, 62, 67]
        }, {
            name: 'Revenue',
            data: [70, 80, 100, 90, 80, 110, 90, 120, 100]
        }, {
            name: 'Free Cash Flow',
            data: [30, 40, 30, 20, 40, 40, 50, 50, 40]
        }],
        xaxis: {
            categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],

        },
        yaxis: {
            title: {
                text: '$ (thousands)'
            }
        },
        fill: {
            opacity: 1

        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "$ " + val + " thousands"
                }
            }
        },

    }

    var chart = new ApexCharts(
        document.querySelector("#chart1"),
        options
    );

    chart.render();

    });
})(jQuery);