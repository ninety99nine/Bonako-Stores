<template>
    <section class="charts">
        <h3 v-if="heading">{{ heading }}</h3>
        <vue-highcharts :options="options" :highcharts="Highcharts" ref="lineCharts"></vue-highcharts>
    </section>
</template>
<script>

import VueHighcharts from "vue2-highcharts";
import Exporting from "highcharts/modules/exporting";
import Highcharts from "highcharts";

Exporting(Highcharts);

export default {
    props: {
        heading: {
            type: String,
            default: ''
        },
        title: {
            type: String,
            default: 'Chart Title'
        },
        subtitle: {
            type: String,
            default: 'Chart Subtitle'
        },
        yAxisText: {
            type: String,
            default: 'y-Axis Label'
        },
        xAxisCategories: {
            type: Array,
            default: function(){
                return [];
            }
        },
        yAxisSeries: {
            type: Array,
            default: function(){
                return [];
            }
        },
        isLoading: {
            type: Boolean,
            default: false
        }

    },
    components: {
        VueHighcharts
    },
    data() {
        return {
            options: {
                chart: {
                    type: 'spline'
                },
                title: {
                    text: this.title
                },
                subtitle: {
                    text: this.subtitle
                },
                xAxis: {
                    categories: this.xAxisCategories,
                    labels: {
                        rotation: -45
                    }
                },
                yAxis: {
                    title: {
                        text: this.yAxisText
                    },
                    labels: {
                        /*
                        formatter: function() {
                            return this.value + "°";
                        }
                        */
                    },
                    allowDecimals: false,
                },
                tooltip: {
                    crosshairs: true,
                    shared: true
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    spline: {
                        marker: {
                            radius: 2,
                            lineColor: "#666666",
                            lineWidth: 1
                        }
                    }
                },
                series: []

            },
            Highcharts: Highcharts
        };
    },
    watch: {

        //  Watch for changes on the isLoading state
        isLoading: {
            handler: function (val, oldVal) {

                if(val == false){

                    this.removeSeries();

                    this.resetCategories();

                    this.load();

                }else{

                    //  Target the chart
                    let lineCharts = this.$refs.lineCharts;

                    //  Show loader
                    lineCharts.showLoading('loading');

                }

            }
        }
    },
    methods: {
        load() {

            //  Target the chart
            let lineCharts = this.$refs.lineCharts;

            //  Hide loader
            lineCharts.hideLoading();

            //  Foreach series
            this.yAxisSeries.forEach((seriesData) => {

                //  If we have data to plot
                if(seriesData.data.length){

                    //  Plot series data
                    lineCharts.addSeries(seriesData);

                }else{

                    //  Chart has no data

                }
             });

            /**
             *  Resize the chart so that it fits properly.
             *  Sometimes the chart is loaded but does not properly
             *  work well when hovering over the points. This will
             *  fix that issue
             */

            lineCharts.chart.reflow();

        },
        resetCategories() {
            //  Target the chart
            let lineCharts = this.$refs.lineCharts;

            //  Update xAxis categories
            lineCharts.getChart().xAxis[0].setCategories(this.xAxisCategories);
        },
        removeSeries() {
            this.$refs.lineCharts.removeSeries();
        },
        setNoDataMessages(){
            this.$refs.lineCharts.renderer.text('No Data Available', 140, 120).css({
                color: '#4572A7',
                fontSize: '16px'
            }).add();
        }
    },
    mounted() {

    }
};
</script>
