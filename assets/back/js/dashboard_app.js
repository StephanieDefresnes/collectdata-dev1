// js
require('startbootstrap-sb-admin-2/vendor/chart.js/Chart.min.js');

// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Arial', '-apple-system,system-ui,BlinkMacSystemFont,Arial,Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

/*
 * Donut Chart: Automatic Chart Colors depending on data count
 */
function calculatePoint(i, intervalSize, colorRangeInfo) {
    let { colorStart, colorEnd, useEndAsStart} = colorRangeInfo;
    return (useEndAsStart
            ? (colorEnd - (i * intervalSize))
            : (colorStart + (i * intervalSize)));
} 

/* Must use an interpolated color scale, which has a range of [0, 1] */
function interpolateColors(dataLength, colorScale, colorRangeInfo) {
    let { colorStart, colorEnd } = colorRangeInfo,
        colorRange = colorEnd - colorStart,
        intervalSize = colorRange / dataLength,
        i, colorPoint,
        colorArray = [],
        colorHoverArray = []

    for (i = 0; i < dataLength; i++) {
        colorPoint = calculatePoint(i, intervalSize, colorRangeInfo)
        colorArray.push(colorScale(colorPoint))
        colorHoverArray.push(colorScale(colorPoint).replace(')', ', 0.75)').replace('rgb', 'rgba'))
    }
    return [colorArray, colorHoverArray];
}

function chartDonut(chartData) {
    const   chartElement = document.getElementById('situsLangChart'),
            dataLength = chartData.data.length,
            colorScale = d3.interpolateCool,
            colorRangeInfo = {
                colorStart: 0.18,
                colorEnd: 0.70,
                useEndAsStart: false,
            };
    
    let COLORS = interpolateColors(dataLength, colorScale, colorRangeInfo)
    
    const donutChart = new Chart(chartElement, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                    data: chartData.data,
                    backgroundColor: COLORS[0],
                    hoverBackgroundColor: COLORS[1],
                    hoverBorderColor: 'rgba(234, 236, 244, 1)',
                }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: { display: false },
            cutoutPercentage: 80,
        },
    });
    return donutChart;
}
function getRandomNumber(min, max) {
    return Math.round(Math.random() * (max - min) + min);
}

/*
 * Area Chart: X-Y values depending on data
 */
function chartArea(chartData, maxDataSitus) {
    const chartElement = document.getElementById('situsMonthChart')
    
    const areaChart = new Chart(chartElement, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: translations["contributions"],
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: chartData.data,
            }],
        },
        options: {
        maintainAspectRatio: false,
            layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
            },
            scales: {
                xAxes: [{
                    time: { unit: 'date' },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: { maxTicksLimit: 7 }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: maxDataSitus,
                        padding: 10,
                        // Include a dollar sign in the ticks
                        callback: function(value, index, values) {
                            return number_format(value);
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: { display: false },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        let datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel +' '+ number_format(tooltipItem.yLabel);
                    }
                }
            }
        }
    })
    return areaChart;
}

/*
 * Bar Chart: X-Y values depending on data
 */
function chartBar(chartData, maxDataUsers) {
    const chartElement = document.getElementById('usersLangChart')
    
    const barChart = new Chart(chartElement, {
    type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: translations["users"],
                backgroundColor: "#4e73df",
                hoverBackgroundColor: "#2e59d9",
                borderColor: "#4e73df",
                data: chartData.data,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: { unit: 'month' },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: { maxTicksLimit: 6 },
                    maxBarThickness: 25,
                }],
                yAxes: [{
                    ticks: {
                        min: 0,
                        max: maxDataUsers,
                        maxTicksLimit: 5,
                        padding: 10,
                        // Include a dollar sign in the ticks
                        callback: function (value, index, values) {
                            return number_format(value);
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: { display: false },
            tooltips: {
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
                callbacks: {
                    label: function (tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel +' '+ number_format(tooltipItem.yLabel);
                    }
                }
            },
        }
    })
    return barChart;
}

function number_format(number, decimals, dec_point, thousands_sep) {
    // *     example: number_format(1234.56, 2, ',', ' ');
    // *     return: '1 234,56'
    number = (number + '').replace(',', '').replace(' ', '');
    let n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            let k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function dataChart() {
    $.ajax({
        url: '/back/ajaxDataChart',
        method: 'GET',
        success: function(data) {
            // Chart Area
            const dataArea = {
                labels: data['situsPerMonth']['dates'],
                data: data['situsPerMonth']['situs'],
            }
            chartArea(dataArea, data['situsPerMonth']['situsMax'])
            
            // Chart Donut
            const dataDonut = {
                labels: data['dataPerLang']['langs'],
                data: data['dataPerLang']['situs'],
            }
            chartDonut(dataDonut)
            
            // Chart Bar
            const dataBar = {
                labels: data['dataPerLang']['langs'],
                data: data['dataPerLang']['users'],
            }
            chartBar(dataBar, data['dataPerLang']['usersMax'])
        }
    })
}

// Set all charts
dataChart()