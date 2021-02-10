<template>
    <div>
        <h1 class="wp-heading-inline">{{ $route.meta.title }}</h1>
        <div class="tile is-ancestor is-vertical">
            <div class="tile">
                <div class="tile is-parent">
                    <chart-block 
                            :chart-series="chartSeries1"
                            :chart-options="chartOptions1" />
                    <chart-block 
                            :chart-series="chartSeries2"
                            :chart-options="chartOptions2" />
                </div>
                <div class="tile is-parent">
                    <chart-block 
                            :chart-series="chartSeries3"
                            :chart-options="chartOptions3" />
                </div>
            </div>
            <div class="tile is-parent">
                <chart-block 
                        :chart-series="chartSeries4"
                        :chart-options="chartOptions4" />
            </div>
        </div>
    </div>
</template>

<script>    
    export default { 
        name: "ReportsList",
        data() {
            return {
                chartSeries1: [44, 55, 13, 43, 22],
                chartOptions1: {
                    chart: {
                        width: 380,
                        type: 'pie',
                    },
                    labels: ['Team A', 'Team B', 'Team C', 'Team D', 'Team E'],

                    title: {
                        text: 'Pie chart'
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                },
                chartSeries2: [
                    {
                        name: 'Marine Sprite',
                        data: [44, 55, 41, 37, 22]
                    }, {
                        name: 'Striking Calf',
                        data: [53, 32, 33, 52, 13]
                    }, {
                        name: 'Tank Picture',
                        data: [12, 17, 11, 9, 15]
                    }, {
                        name: 'Bucket Slope',
                        data: [9, 7, 5, 8, 6]
                    }
                ],
                chartOptions2: {
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: true,
                        stackType: '100%'
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                        },
                    },
                    stroke: {
                        width: 1,
                        colors: ['#fff']
                    },
                    title: {
                        text: '100% Stacked Bar'
                    },
                    xaxis: {
                        categories: [2008, 2009, 2010, 2011, 2012],
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                            return val + "K"
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        offsetX: 40
                    }
                },
                chartSeries3: [
                    {
                        data: [
                            {
                                x: 'New Delhi',
                                y: 218
                            },
                            {
                                x: 'Kolkata',
                                y: 149
                            },
                            {
                                x: 'Mumbai',
                                y: 184
                            },
                            {
                                x: 'Ahmedabad',
                                y: 55
                            },
                            {
                                x: 'Bangaluru',
                                y: 84
                            },
                            {
                                x: 'Pune',
                                y: 31
                            },
                            {
                                x: 'Chennai',
                                y: 70
                            },
                            {
                                x: 'Jaipur',
                                y: 30
                            },
                            {
                                x: 'Surat',
                                y: 44
                            },
                            {
                                x: 'Hyderabad',
                                y: 68
                            },
                            {
                                x: 'Lucknow',
                                y: 28
                            },
                            {
                                x: 'Indore',
                                y: 19
                            },
                            {
                                x: 'Kanpur',
                                y: 29
                            }
                        ]
                    }
                ],
                chartOptions3: {
                    legend: {
                        show: false
                    },
                    chart: {
                        height: 350,
                        type: 'treemap'
                    },
                    title: {
                        text: 'Basic Treemap'
                    }
                },
                chartSeries4: [
                    {
                        name: 'Net Profit',
                        data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
                    }, {
                        name: 'Revenue',
                        data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
                    }, {
                        name: 'Free Cash Flow',
                        data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
                    }
                ],
                chartOptions4: {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
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
                    xaxis: {
                        categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                    },
                    yaxis: {
                        title: {
                            text: '$ (thousands)'
                        }
                    },
                    title: {
                        text: 'Vertical columns'
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
                    }
                },
            }
        }
    }
</script>

<style lang="scss" scoped>
    
</style>
