<template>

    <Row :gutter="12">

        <Col :span="20" :offset="2">

            <!-- Heading & Watch Video Button -->
            <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                <Col :span="12">

                    <!-- Heading -->
                    <h1 :class="['font-weight-bold', 'text-muted']">Reports</h1>

                </Col>

                <Col :span="12">

                    <div class="clearfix">

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" @click.native="fetchReportStatistics()" :class="['float-right']">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

            </Row>

            <!-- Date Picker, Accuracy Filter & Refresh Button -->
            <Row :gutter="12" class="mb-2">

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

            <!-- Store Selection & Location Selection Inputs -->
            <Row :gutter="12" class="mb-5">

                <Col :span="6">

                    <!-- Store -->
                    <div class="d-flex align-items-center">
                        <span class="font-weight-bold text-nowrap mr-1">Store:</span>

                        <!-- Store Select -->
                        <Select placeholder="Search store" icon="ios-search-outline" loading-text="Searching stores..."
                                not-found-text="No stores found" prefix="ios-search-outline" filterable :remote-method="searchStore"
                                :loading="isSearchingStores" @on-select="handleSelectedStore($event)" clearable @on-clear="storeId = null; fetchReportStatistics()">

                            <Option v-for="(store, index) in stores" :value="store.id" :key="index"
                                    :label="store.name">

                                <div class="d-flex">
                                    <Icon type="ios-pin-outline" :size="20" :class="['text-primary', 'mr-1']"/>
                                    <div>
                                        <span :class="['font-weight-bold']">{{ store.name }}</span>
                                        <span> - {{ getVisitShortCodeDialingCode(store) }}</span>
                                    </div>
                                </div>
                            </Option>

                        </Select>
                    </div>

                </Col>

                <Col :span="12">

                    Location selector

                </Col>

            </Row>

            <!-- Charts -->
            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Stores Over Time" subtitle="Record of stores created over a period of time" yAxisText="Stores" :xAxisCategories="storeCreationOverTime.categories" :yAxisSeries="storeCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

                <Col :span="12">

                    <lineChart title="Locations Over Time" subtitle="Record of locations created over a period of time" yAxisText="Locations" :xAxisCategories="locationCreationOverTime.categories" :yAxisSeries="locationCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Products Over Time" subtitle="Record of products created over a period of time" yAxisText="Products" :xAxisCategories="productCreationOverTime.categories" :yAxisSeries="productCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

                <Col :span="12">

                    <lineChart title="Product Existence Over Time" subtitle="Record of location product existence during shopping over a period of time" yAxisText="Product Existence" :xAxisCategories="productExistenceOverTime.categories" :yAxisSeries="productExistenceOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Coupons Over Time" subtitle="Record of coupons created over a period of time" yAxisText="Coupons" :xAxisCategories="couponCreationOverTime.categories" :yAxisSeries="couponCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

                <Col :span="12">

                    <lineChart title="Coupon Existence Over Time" subtitle="Record of location coupon existence during shopping over a period of time" yAxisText="Coupon Existence" :xAxisCategories="couponExistenceOverTime.categories" :yAxisSeries="couponExistenceOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Instant Carts Over Time" subtitle="Record of instant carts created over a period of time" yAxisText="Instant Carts" :xAxisCategories="instantCartCreationOverTime.categories" :yAxisSeries="instantCartCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

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

                    <lineChart title="Subscriptions Over Time" subtitle="Record of subscriptions created over a period of time" yAxisText="Subscriptions" :xAxisCategories="subscriptionCreationOverTime.categories" :yAxisSeries="subscriptionCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

                <Col :span="12">

                    <lineChart title="Subscription Revenue Over Time" subtitle="Record of subscription revenue over a period of time" yAxisText="Revenue" :xAxisCategories="subscriptionRevenueOverTime.categories" :yAxisSeries="subscriptionRevenueOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Transactions Over Time" subtitle="Record of transactions created over a period of time" yAxisText="Transactions" :xAxisCategories="transactionCreationOverTime.categories" :yAxisSeries="transactionCreationOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

                <Col :span="12">

                    <lineChart title="Transaction Revenue Over Time" subtitle="Record of transaction revenue over a period of time" yAxisText="Transactions" :xAxisCategories="transactionRevenueOverTime.categories" :yAxisSeries="transactionRevenueOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <lineChart title="Short Codes Over Time" subtitle="Record of short codes over a period of time" yAxisText="Shortcodes" :xAxisCategories="shortCodeOverTime.categories" :yAxisSeries="shortCodeOverTime.seriesData" :isLoading="isLoading"></lineChart>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <pieChart title="Subscription Plan Traffic" subtitle="Preffered subscription plan" :series="subscriptionTrafficByPlanName" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['subscription_traffic_by_plan_name']" :summary="reportStatistics['subscription_traffic_by_plan_name']['summary']"></chartSummary>

                </Col>

                <Col :span="12">

                    <pieChart title="Subscription Plan Revenue" subtitle="Revenue of by subscription plans" :series="subscriptionRevenueByPlanName" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['subscription_revenue_by_plan_name']" :summary="reportStatistics['subscription_revenue_by_plan_name']['summary']"></chartSummary>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <pieChart title="Subscription Traffic Sources" subtitle="Origin of subscription" :series="subscriptionTrafficBySourceTypes" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['subscription_traffic_by_source_types']" :summary="reportStatistics['subscription_traffic_by_source_types']['summary']"></chartSummary>

                </Col>

                <Col :span="12">

                    <pieChart title="Subscription Revenue Sources" subtitle="Revenue of subscription by source" :series="subscriptionRevenueBySourceTypes" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['subscription_revenue_by_source_types']" :summary="reportStatistics['subscription_revenue_by_source_types']['summary']"></chartSummary>

                </Col>



            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <pieChart title="Transaction Payment Method Traffic" subtitle="Preferred online payment methods" :series="transactionByPaymentMethods" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['transaction_by_payment_methods']" :summary="reportStatistics['transaction_by_payment_methods']['summary']"></chartSummary>

                </Col>

                <Col :span="12">

                    <pieChart title="Transaction Payment Method Revenue" subtitle="Revenue of online payments by payment method" :series="transactionRevenueByPaymentMethods" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['transaction_revenue_by_payment_methods']" :summary="reportStatistics['transaction_revenue_by_payment_methods']['summary']"></chartSummary>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <pieChart title="Transaction Traffic Sources" subtitle="Origin of online payment traffic" :series="transactionTrafficBySourceTypes" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['transaction_traffic_by_source_types']" :summary="reportStatistics['transaction_traffic_by_source_types']['summary']"></chartSummary>

                </Col>

                <Col :span="12">

                    <pieChart title="Transaction Revenue Sources" subtitle="Revenue of online payments by source" :series="transactionRevenueBySourceTypes" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['transaction_revenue_by_source_types']" :summary="reportStatistics['transaction_revenue_by_source_types']['summary']"></chartSummary>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <pieChart title="Short Code By Activity" subtitle="Purpose of shortcode" :series="shortCodeTrafficByAction" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['short_code_traffic_by_action']" :summary="reportStatistics['short_code_traffic_by_action']['summary']"></chartSummary>

                </Col>

                <Col :span="12">

                    <pieChart title="Short Code By Sources" subtitle="Allocation of shortcodes to resources" :series="shortCodeTrafficBySources" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['short_code_traffic_by_sources']" :summary="reportStatistics['short_code_traffic_by_sources']['summary']"></chartSummary>

                </Col>

            </Row>

            <Row :gutter="12" class="mb-4">

                <Col :span="12">

                    <pieChart title="Location Products By Existence" subtitle="Percentage of locations with or without products during user shopping" :series="locationProductByExistence" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['location_product_by_existence']" :summary="reportStatistics['location_product_by_existence']['summary']"></chartSummary>

                </Col>

                <Col :span="12">

                    <pieChart title="Location Coupons By Existence" subtitle="Percentage of locations with or without coupons during user shopping" :series="locationCouponByExistence" :isLoading="isLoading"></pieChart>
                    <chartSummary v-if="reportStatistics['location_coupon_by_existence']" :summary="reportStatistics['location_coupon_by_existence']['summary']"></chartSummary>

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

    import reportMixin from './mixin.vue';

    export default {
        mixins: [reportMixin],
        data(){
            return {
                stores: [],
                storeId: null,
                locations: [],
                location: null,
                isSearchingStores: false
            }
        },
        computed: {
            storesUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:stores'].href
            },
            locationsUrl(){
                if(this.store){
                    return this.store['_links']['bos:locations'].href
                }
            }
        },
        methods: {
            getVisitShortCodeDialingCode(store){
                return (store['_attributes']['visit_short_code'] || {}).dialing_code;
            },
            handleSelectedStore(event){

                //  Set the store id
                this.storeId = event.value;

                //  Navigate to the order
                this.fetchReportStatistics();

            },
            /**
             *  Search stores only 1 second after the user is done typing.
             */
            searchStore(searchWord) {

                //  Reset the stores
                this.stores = [];

                //  If we have a search word
                if( searchWord ){

                    //  Clear the search timeout variable
                    clearTimeout(this.searchTimeout);

                    this.searchTimeout = setTimeout(() => {

                        //  Get the stores
                        this.fetchStores(searchWord);

                    }, 1000); // 1 sec delay

                }
            },
            fetchStores(searchWord) {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSearchingStores = true;

                //  Use the api call() function, refer to api.js
                api.call('get', this.storesUrl+'?search='+searchWord)
                    .then(({data}) => {

                        //  Get the stores
                        self.stores = (((data || {})._embedded || {}).stores || []);

                        //  Stop loader
                        self.isSearchingStores = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isSearchingStores = false;

                    });
            },

        },
        created(){

            //  Change dashboard heading
            this.$emit('changeHeading', 'Bonako Online');

        }
    }
</script>
