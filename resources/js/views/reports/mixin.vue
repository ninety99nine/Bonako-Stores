<script>

    import pieChart from './components/pieChart.vue';
    import lineChart from './components/lineChart.vue';
    import chartSummary from './components/chartSummary.vue';
    import dateRangePickerInput from './components/dateRangePickerInput.vue';

    export default {
        components: {
            pieChart, lineChart, chartSummary, dateRangePickerInput
        },
        data(){
            return {
                isLoading: false,
                reportStatistics: [],
                selectedFilters: [],
                reportTypes: [
                    {
                        name: 'Creation',
                        desc: 'Creation of resources'
                    }
                ],
                selectedAccuracyType: 'Days',
                accuracyTypes: [
                    {
                        name: 'Seconds'
                    },
                    {
                        name: 'Minutes'
                    },
                    {
                        name: 'Hours'
                    },
                    {
                        name: 'Days'
                    },
                    {
                        name: 'Months'
                    },
                    {
                        name: 'Years'
                    }
                ],
                dateRange: []
            }
        },
        computed: {
            reportStatisticsUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:report_statistics'].href
            },
            storeCreationOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisRemoved = [];

                if( this.reportStatistics['store_report_over_time'] ){
                    xAxis = this.reportStatistics['store_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['store_report_over_time']['created'].map((report) => { return report.count });
                    yAxisRemoved = this.reportStatistics['store_report_over_time']['removed'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Removed",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisRemoved
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            locationCreationOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisRemoved = [];

                if( this.reportStatistics['location_report_over_time'] ){
                    xAxis = this.reportStatistics['location_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['location_report_over_time']['created'].map((report) => { return report.count });
                    yAxisRemoved = this.reportStatistics['location_report_over_time']['removed'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Removed",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisRemoved
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            orderCreationOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisCancelled = [];

                if( this.reportStatistics['order_report_over_time'] ){
                    xAxis = this.reportStatistics['order_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['order_report_over_time']['created'].map((report) => { return report.count });
                    yAxisCancelled = this.reportStatistics['order_report_over_time']['cancelled'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Cancelled",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisCancelled
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            cartCreationOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisRecovered = [], yAxisConverted = [], yAxisAbandoned = [];

                if( this.reportStatistics['cart_report_over_time'] ){
                    xAxis = this.reportStatistics['cart_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['cart_report_over_time']['created'].map((report) => { return report.count });
                    yAxisRecovered = this.reportStatistics['cart_report_over_time']['recovered'].map((report) => { return report.count });
                    yAxisConverted = this.reportStatistics['cart_report_over_time']['converted'].map((report) => { return report.count });
                    yAxisAbandoned = this.reportStatistics['cart_report_over_time']['abandoned'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Converted",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#8bbc21',
                            data: yAxisConverted
                        },
                        {
                            name: "Abandoned",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisAbandoned
                        },
                        {
                            name: "Recovered",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#DDDF00',
                            data: yAxisRecovered
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            cartPotentialOverTime(){

                var xAxis = [], yAxisSubtotal = [], yAxisGrandTotal = [], yAxisDiscountTotal = [], yAxisDeliveryFeeTotal = [];

                if( this.reportStatistics['cart_report_over_time'] ){
                    xAxis = this.reportStatistics['cart_report_over_time']['potential'].map((report) => { return report.name });
                    yAxisSubtotal = this.reportStatistics['cart_report_over_time']['potential'].map((report) => { return report.sub_total });
                    yAxisGrandTotal = this.reportStatistics['cart_report_over_time']['potential'].map((report) => { return report.grand_total });
                    yAxisDiscountTotal = this.reportStatistics['cart_report_over_time']['potential'].map((report) => { return report.coupon_and_sale_discount_total });
                    yAxisDeliveryFeeTotal = this.reportStatistics['cart_report_over_time']['potential'].map((report) => { return report.delivery_fee_total });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Sub Total",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisSubtotal
                        },
                        {
                            name: "Discount",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisDiscountTotal
                        },
                        {
                            name: "Delivery Fee",
                            color: '#24CBE5',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisDeliveryFeeTotal
                        },
                        {
                            name: "Grand Total",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#8bbc21',
                            data: yAxisGrandTotal
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            cartConvertedOverTime(){

                var xAxis = [], yAxisSubtotal = [], yAxisGrandTotal = [], yAxisDiscountTotal = [], yAxisDeliveryFeeTotal = [];

                if( this.reportStatistics['cart_report_over_time'] ){
                    xAxis = this.reportStatistics['cart_report_over_time']['converted'].map((report) => { return report.name });
                    yAxisSubtotal = this.reportStatistics['cart_report_over_time']['converted'].map((report) => { return report.sub_total });
                    yAxisGrandTotal = this.reportStatistics['cart_report_over_time']['converted'].map((report) => { return report.grand_total });
                    yAxisDiscountTotal = this.reportStatistics['cart_report_over_time']['converted'].map((report) => { return report.coupon_and_sale_discount_total });
                    yAxisDeliveryFeeTotal = this.reportStatistics['cart_report_over_time']['converted'].map((report) => { return report.delivery_fee_total });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Sub Total",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisSubtotal
                        },
                        {
                            name: "Discount",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisDiscountTotal
                        },
                        {
                            name: "Delivery Fee",
                            color: '#24CBE5',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisDeliveryFeeTotal
                        },
                        {
                            name: "Grand Total",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#8bbc21',
                            data: yAxisGrandTotal
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            productCreationOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisRemoved = [];

                if( this.reportStatistics['product_report_over_time'] ){
                    xAxis = this.reportStatistics['product_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['product_report_over_time']['created'].map((report) => { return report.count });
                    yAxisRemoved = this.reportStatistics['product_report_over_time']['removed'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Removed",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisRemoved
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            couponCreationOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisRemoved = [];

                if( this.reportStatistics['coupon_report_over_time'] ){
                    xAxis = this.reportStatistics['coupon_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['coupon_report_over_time']['created'].map((report) => { return report.count });
                    yAxisRemoved = this.reportStatistics['coupon_report_over_time']['removed'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Removed",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisRemoved
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            transactionCreationOverTime(){

                var xAxis = [], yAxis = [];

                if( this.reportStatistics['transaction_report_over_time'] ){
                    xAxis = this.reportStatistics['transaction_report_over_time']['created'].map((report) => { return report.name });
                    yAxis = this.reportStatistics['transaction_report_over_time']['created'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Total",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxis
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            transactionRevenueOverTime(){

                var xAxis = [], yAxis = [];

                if( this.reportStatistics['transaction_report_over_time'] ){
                    xAxis = this.reportStatistics['transaction_report_over_time']['created'].map((report) => { return report.name });
                    yAxis = this.reportStatistics['transaction_report_over_time']['created'].map((report) => { return report.amount });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Revenue",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxis
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            subscriptionCreationOverTime(){

                var xAxis = [], yAxis = [];

                if( this.reportStatistics['subscription_report_over_time'] ){
                    xAxis = this.reportStatistics['subscription_report_over_time']['created'].map((report) => { return report.name });
                    yAxis = this.reportStatistics['subscription_report_over_time']['created'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Total",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxis
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            subscriptionRevenueOverTime(){

                var xAxis = [], yAxis = [];

                if( this.reportStatistics['subscription_report_over_time'] ){
                    xAxis = this.reportStatistics['subscription_report_over_time']['created'].map((report) => { return report.name });
                    yAxis = this.reportStatistics['subscription_report_over_time']['created'].map((report) => { return report.amount });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Revenue",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxis
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            instantCartCreationOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisRemoved = [];

                if( this.reportStatistics['instant_cart_report_over_time'] ){
                    xAxis = this.reportStatistics['instant_cart_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['instant_cart_report_over_time']['created'].map((report) => { return report.count });
                    yAxisRemoved = this.reportStatistics['instant_cart_report_over_time']['removed'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Removed",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisRemoved
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            shortCodeOverTime(){

                var xAxis = [], yAxisCreated = [], yAxisRecycled = [];

                if( this.reportStatistics['short_code_report_over_time'] ){
                    xAxis = this.reportStatistics['short_code_report_over_time']['created'].map((report) => { return report.name });
                    yAxisCreated = this.reportStatistics['short_code_report_over_time']['created'].map((report) => { return report.count });
                    yAxisRecycled = this.reportStatistics['short_code_report_over_time']['recycled'].map((report) => { return report.count });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Created",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisCreated
                        },
                        {
                            name: "Recycled",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#8bbc21',
                            data: yAxisRecycled
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            productExistenceOverTime(){

                var xAxis = [], yAxisExistence = [], yAxisNonExistence = [];

                if( this.reportStatistics['location_report_over_time'] ){
                    xAxis = this.reportStatistics['location_report_over_time']['product existence'].map((report) => { return report.name });
                    yAxisExistence = this.reportStatistics['location_report_over_time']['product existence'].map((report) => { return report.count_existence });
                    yAxisNonExistence = this.reportStatistics['location_report_over_time']['product existence'].map((report) => { return report.count_non_existence });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Existence",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisExistence
                        },
                        {
                            name: "Non Existence",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisNonExistence
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },
            couponExistenceOverTime(){

                var xAxis = [], yAxisExistence = [], yAxisNonExistence = [];

                if( this.reportStatistics['location_report_over_time'] ){
                    xAxis = this.reportStatistics['location_report_over_time']['coupon existence'].map((report) => { return report.name });
                    yAxisExistence = this.reportStatistics['location_report_over_time']['coupon existence'].map((report) => { return report.count_existence });
                    yAxisNonExistence = this.reportStatistics['location_report_over_time']['coupon existence'].map((report) => { return report.count_non_existence });
                }

                return {
                    categories: xAxis,
                    seriesData: [
                        {
                            name: "Existence",
                            marker: {
                                symbol: "circle"
                            },
                            color: '#2f7ed8',
                            data: yAxisExistence
                        },
                        {
                            name: "Non Existence",
                            color: '#FF0000',
                            marker: {
                                symbol: "circle",
                            },
                            data: yAxisNonExistence
                        },
                        //  Add more series data objects here (Each series data object will produce a new line)
                    ],
                }
            },




            //  Pie chart data
            transactionByPaymentMethods(){

                var chartData = [];

                if( this.reportStatistics['transaction_by_payment_methods'] ){
                    chartData = this.reportStatistics['transaction_by_payment_methods']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            transactionTrafficBySourceTypes(){

                var chartData = [];

                if( this.reportStatistics['transaction_traffic_by_source_types'] ){
                    chartData = this.reportStatistics['transaction_traffic_by_source_types']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            transactionRevenueBySourceTypes(){

                var chartData = [];

                if( this.reportStatistics['transaction_revenue_by_source_types'] ){
                    chartData = this.reportStatistics['transaction_revenue_by_source_types']['data'].map((report) => { return [report.name, report.amount] });
                }

                return [
                    {
                        name: "Revenue",
                        data: chartData
                    }
                ];
            },
            transactionRevenueByPaymentMethods(){

                var chartData = [];

                if( this.reportStatistics['transaction_revenue_by_payment_methods'] ){
                    chartData = this.reportStatistics['transaction_revenue_by_payment_methods']['data'].map((report) => { return [report.name, report.amount] });
                }

                return [
                    {
                        name: "Revenue",
                        data: chartData
                    }
                ];
            },
            subscriptionTrafficByPlanName(){

                var chartData = [];

                if( this.reportStatistics['subscription_traffic_by_plan_name'] ){
                    chartData = this.reportStatistics['subscription_traffic_by_plan_name']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            subscriptionRevenueByPlanName(){

                var chartData = [];

                if( this.reportStatistics['subscription_revenue_by_plan_name'] ){
                    chartData = this.reportStatistics['subscription_revenue_by_plan_name']['data'].map((report) => { return [report.name, report.amount] });
                }

                return [
                    {
                        name: "Revenue",
                        data: chartData
                    }
                ];
            },
            subscriptionTrafficBySourceTypes(){

                var chartData = [];

                if( this.reportStatistics['subscription_traffic_by_source_types'] ){
                    chartData = this.reportStatistics['subscription_traffic_by_source_types']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            subscriptionRevenueBySourceTypes(){

                var chartData = [];

                if( this.reportStatistics['subscription_revenue_by_source_types'] ){
                    chartData = this.reportStatistics['subscription_revenue_by_source_types']['data'].map((report) => { return [report.name, report.amount] });
                }

                return [
                    {
                        name: "Revenue",
                        data: chartData
                    }
                ];
            },
            shortCodeTrafficByAction(){

                var chartData = [];

                if( this.reportStatistics['short_code_traffic_by_action'] ){
                    chartData = this.reportStatistics['short_code_traffic_by_action']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            shortCodeTrafficBySources(){

                var chartData = [];

                if( this.reportStatistics['short_code_traffic_by_sources'] ){
                    chartData = this.reportStatistics['short_code_traffic_by_sources']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            locationProductByExistence(){

                var chartData = [];

                if( this.reportStatistics['location_product_by_existence'] ){
                    chartData = this.reportStatistics['location_product_by_existence']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            locationCouponByExistence(){

                var chartData = [];

                if( this.reportStatistics['location_coupon_by_existence'] ){
                    chartData = this.reportStatistics['location_coupon_by_existence']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            cartByConversionAndAbandonment(){

                var chartData = [];

                if( this.reportStatistics['cart_by_conversion_and_abandonment'] ){
                    chartData = this.reportStatistics['cart_by_conversion_and_abandonment']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
            cartByAbandonmentAndRecovery(){

                var chartData = [];

                if( this.reportStatistics['cart_by_abandonment_and_recovery'] ){
                    chartData = this.reportStatistics['cart_by_abandonment_and_recovery']['data'].map((report) => { return [report.name, report.count] });
                }

                return [
                    {
                        name: "Total",
                        data: chartData
                    }
                ];
            },
        },
        methods: {
            fetchReportStatistics() {
                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the report statistics url
                    if( this.reportStatisticsUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        //
                        var reportTypes = this.selectedFilters.join(',');

                        var start_date = this.dateRange.length ? this.dateRange[0] : '';
                        var end_date = this.dateRange.length ? this.dateRange[1] : '';

                        //  If we have a store id, then set the store id
                        var storeIdQuery = this.storeId ? '&store_id='+this.storeId : '';

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.reportStatisticsUrl+'?type='+reportTypes+'&accuracy='+this.selectedAccuracyType+'&start_date='+start_date+'&end_date='+end_date+storeIdQuery)
                            .then(({data}) => {

                                //  Get the report statistics
                                self.reportStatistics = data || [];

                                //  Stop loader
                                self.isLoading = false;

                            })
                            .catch(response => {

                                //  Stop loader
                                self.isLoading = false;

                            });
                    }

                });

            }
        },
        created(){

            //  Get the report statistics
            this.fetchReportStatistics();

        }
    }
</script>
