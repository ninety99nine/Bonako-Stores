<template>
    <section class="charts">
        <h3 v-if="heading">{{ heading }}</h3>
        <vue-highcharts :key="renderKey" :options="options" :highcharts="Highcharts" ref="lineCharts" class="w-100"></vue-highcharts>
    </section>
</template>

<style>
    .highcharts-tooltip span {
        height:auto !important;
        width:140px !important;
        max-width:140px !important;
        overflow:auto !important;
        white-space:normal !important;
    }
</style>

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
        series: {
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
            renderKey: 1,
            Highcharts: Highcharts
        };
    },
    watch: {

        //  Watch for changes on the isLoading state
        isLoading: {
            handler: function (val, oldVal) {

                if(val == false){

                    //  Force chart re-render
                    this.rerenderChart();

                }

            }
        }
    },
    computed: {
        options(){
            return {
                chart: {
                    type: "pie",
                    options3d: {
                        enabled: true,
                        alpha: 45
                    }
                },
                title: {
                    text: this.title
                },
                subtitle: {
                    text: this.subtitle
                },
                tooltip: {
                    useHTML: true,
                    pointFormat: '<br>{series.name}: {point.y}<br>Percentage: {point.percentage:.1f}%'
                },
                plotOptions: {
                    pie: {
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b> - {point.percentage:.0f}%',
                        },
                        innerSize: 150,
                        depth: 0
                    }
                },
                series: this.series
            }
        }
    },
    methods: {
        rerenderChart(){

            //  Force chart re-render
            ++this.renderKey

        }
    },
    mounted() {

    }
};
</script>
