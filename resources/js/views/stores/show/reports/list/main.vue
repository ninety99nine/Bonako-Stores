<template>

    <Row class="mt-4">

        <Col :span="22" :offset="1">

            <!-- Heading, Add Coupon Button & Watch Video Button -->
            <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                <Col :span="12">

                    <!-- Heading -->
                    <h1 :class="['font-weight-bold', 'text-muted']">Reports</h1>

                </Col>

                <Col :span="12">

                    <div class="clearfix">

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" @click.native="fetchCoupons()" :class="['float-right']">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

            </Row>

            <!-- Date Picker, Accuracy Filter & Refresh Button -->
            <Row :gutter="12" class="mb-5">

                <Col :span="10">

                    <!-- Date Picker -->
                    <div class="d-flex align-items-center">
                        <span class="font-weight-bold text-nowrap mr-1">Date Range:</span>
                        <dateRangePickerInput :dateRange="dateRange" @updated="dateRange = $event; fetchReportStatistics()" class="w-100"></dateRangePickerInput>
                    </div>

                </Col>

                <Col :span="14">

                    <Row :gutter="12">

                        <Col :span="10">

                            <!-- Accuracy Filters -->
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold mr-1">Accuracy:</span>
                                <Poptip trigger="hover" content="Select chart accuracy" word-wrap class="poptip-w-100">
                                    <Select v-model="selectedAccuracyType" size="default" class="w-100"
                                            placeholder="Add filters" :disabled="isLoading" @on-select="fetchReportStatistics()">

                                        <!-- Filter Options-->
                                        <Option v-for="(accuracyType, index) in accuracyTypes" :value="accuracyType.name" :key="index" :label="accuracyType.name">
                                            <span :class="['font-weight-bold']">{{ accuracyType.name }}</span>
                                        </Option>

                                    </Select>
                                </Poptip>
                            </div>

                        </Col>

                        <Col :span="8">

                            <!-- Filters -->
                            <Poptip trigger="hover" content="Add filters for report statistics" word-wrap class="poptip-w-100">
                                <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                        prefix="ios-funnel-outline" clearable placeholder="Add filters"
                                        :disabled="isLoading" @on-select="fetchReportStatistics()">

                                    <!-- Filter Options-->
                                    <Option v-for="(reportType, index) in reportTypes" :value="reportType.name" :key="index" :label="reportType.name">
                                        <span :class="['font-weight-bold']">{{ reportType.name }}</span>
                                        <span v-if="reportType.desc" style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">{{ reportType.desc }}</span>
                                    </Option>

                                </Select>
                            </Poptip>

                        </Col>

                        <Col :span="6">

                            <div :class="['clearfix']">

                                <!-- Refresh Button -->
                                <Button type="default" size="default" :class="['float-right']"
                                        @click.native="fetchReportStatistics()" :loading="isLoading"
                                        :disabled="isLoading">
                                    <Icon v-show="!isLoading" type="ios-refresh" :size="20" />
                                    <span>Refresh</span>
                                </Button>

                            </div>

                        </Col>

                    </Row>

                </Col>

            </Row>

            <!-- Charts -->
            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Carts Over Time" subtitle="Record of carts created over a period of time" yAxisText="Carts" :xAxisCategories="cartCreationOverTime.categories" :yAxisSeries="cartCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

                <Col :span="12">

                    <lineChart title="Cart Potential Conversion Revenue Over Time" subtitle="Record of cart potential revenue, discounts & delivery fees over a period of time" yAxisText="Amount" :xAxisCategories="cartPotentialOverTime.categories" :yAxisSeries="cartPotentialOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Orders Over Time" subtitle="Record of orders created over a period of time" yAxisText="Orders" :xAxisCategories="orderCreationOverTime.categories" :yAxisSeries="orderCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

                <Col :span="12">

                    <lineChart title="Cart Actual Conversion Revenue Over Time" subtitle="Record of cart actual revenue, discounts & delivery fees over a period of time" yAxisText="Amount" :xAxisCategories="cartConvertedOverTime.categories" :yAxisSeries="cartConvertedOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <pieChart title="Cart Conversion Vs Abandonment" subtitle="Percentage of carts that are converted vs abandoned" :series="cartByConversionAndAbandonment" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['cart_by_conversion_and_abandonment']" :summary="reportStatistics['cart_by_conversion_and_abandonment']['summary']"></chartSummary>

                </Col>

                <Col :span="12">

                    <pieChart title="Cart Abandonment Vs Recovery" subtitle="Percentage of carts that are abandoned vs recovered" :series="cartByAbandonmentAndRecovery" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['cart_by_abandonment_and_recovery']" :summary="reportStatistics['cart_by_abandonment_and_recovery']['summary']"></chartSummary>

                </Col>

            </Row>

        </Col>

    </Row>

</template>

<script>

    import reportMixin from './../../../../reports/mixin.vue';

    export default {
        mixins: [reportMixin],
        components: {

        },
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            assignedLocations: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        data(){
            return {

            }
        },
        computed: {
            reportStatisticsUrl(){
                return this.location['_links']['bos:report_statistics'].href;
            }
        },
        methods: {

        },
        created(){

        }
    };

</script>
