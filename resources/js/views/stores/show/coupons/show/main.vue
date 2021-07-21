<template>

    <Row :class="layoutSize == 'large' ? ['mt-4'] : []">

        <Col :span="22" :offset="1">

            <Row :gutter="12">

                <Col :span="24" :class=" layoutSize == 'large' ? ['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4'] : []">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="(isEditing && isLoadingCoupon)" class="mb-2">Searching coupon</Loader>

                    <!-- Coupon Name, Statuses & Watch Video Button -->
                    <Row v-else-if="(isEditing && !isLoadingCoupon && localCoupon) || !isEditing" :gutter="12">

                        <Col :span="24">

                            <!-- If we are loading, Show Loader -->
                            <Loader v-if="isCreating" class="mb-2">Creating coupon</Loader>
                            <Loader v-else-if="isSavingChanges" class="mb-2">Saving coupon</Loader>
                            <Loader v-else-if="!couponForm" class="mb-2">Preparing coupon</Loader>

                            <Form v-if="couponForm" ref="couponForm" :model="couponForm" :rules="couponFormRules">

                                <!-- Toggle Active Switch -->
                                <activeSwitch :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors" class="mb-2"></activeSwitch>

                                <!-- Enter Name -->
                                <nameInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></nameInput>

                                <!-- Enter Description -->
                                <descriptionInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></descriptionInput>

                                <!-- Discount Settings Heading -->
                                <Divider orientation="left" class="font-weight-bold">Discount Settings</Divider>

                                <!-- Apply Discount Checkbox -->
                                <applyDiscountCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></applyDiscountCheckbox>

                                <Row v-if="couponForm.apply_discount" :gutter="12">

                                    <Col :span="12">

                                        <!-- Discount Rate Type Select Input -->
                                        <discountRateTypeSelectInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountRateTypeSelectInput>

                                    </Col>

                                    <Col :span="12">

                                        <!-- Discount Rate Type Select Input -->
                                        <fixedRateInput :location="location" :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></fixedRateInput>

                                        <!-- Discount Rate Type Select Input -->
                                        <percentageRateInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></percentageRateInput>

                                    </Col>

                                </Row>

                                <!-- Discount Settings Heading -->
                                <Divider orientation="left" class="font-weight-bold">Delivery Settings</Divider>

                                <!-- Allow Free Delivery Checkbox -->
                                <allowFreeDeliveryCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowFreeDeliveryCheckbox>

                                <!-- Free Delivery Alert -->
                                <freeDeliveryAlert :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></freeDeliveryAlert>

                                <!-- Discount Rules Heading -->
                                <Divider orientation="left" class="font-weight-bold">Activation Settings</Divider>


                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Always Apply Checkbox -->
                                        <activationTypeSelectInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></activationTypeSelectInput>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Code Input -->
                                        <codeInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></codeInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Minimum Total Checkbox -->
                                        <allowDiscountOnMinimumTotalCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnMinimumTotalCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Minimum Total Input -->
                                        <discountOnMinimumTotalInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnMinimumTotalInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Total Items Checkbox -->
                                        <allowDiscountOnTotalItemsCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnTotalItemsCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Minimum Total Input -->
                                        <discountOnTotalItemsInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnTotalItemsInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Total Unique Items Checkbox -->
                                        <allowDiscountOnTotalUniqueItemsCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnTotalUniqueItemsCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Total Unique Items Input -->
                                        <discountOnTotalUniqueItemsInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnTotalUniqueItemsInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Usage Limit Checkbox -->
                                        <allowUsageLimitCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowUsageLimitCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Usage Limit Input -->
                                        <usageLimitInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></usageLimitInput>

                                        <!-- Usage Quantity Input -->
                                        <usageQuantityInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></usageQuantityInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Start Datetime Checkbox -->
                                        <allowDiscountOnStartDatetimeCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnStartDatetimeCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Start Datetime Input -->
                                        <discountOnStartDatetimeLimitInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnStartDatetimeLimitInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On End Datetime Checkbox -->
                                        <allowDiscountOnEndDatetimeCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnEndDatetimeCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On End Datetime Input -->
                                        <discountOnEndDatetimeLimitInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnEndDatetimeLimitInput>

                                    </Col>

                                </Row>

                                <!-- START FROM HERE -->
                                <!-- START FROM HERE -->
                                <!-- START FROM HERE -->

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Times Checkbox -->
                                        <allowDiscountOnTimesCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnTimesCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Times Input -->
                                        <discountOnTimesInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnTimesInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Days Of The Week Checkbox -->
                                        <allowDiscountOnDaysOfTheWeekCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnDaysOfTheWeekCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Days Of The Week Input -->
                                        <discountOnDaysOfTheWeekInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnDaysOfTheWeekInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Days Of The Month Checkbox -->
                                        <allowDiscountOnDaysOfTheMonthCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnDaysOfTheMonthCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Days Of The Month Input -->
                                        <discountOnDaysOfTheMonthInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnDaysOfTheMonthInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="14">

                                        <!-- Allow Discount On Months Of The Year Checkbox -->
                                        <allowDiscountOnMonthsOfTheYearCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnMonthsOfTheYearCheckbox>

                                    </Col>

                                    <Col :span="10">

                                        <!-- Discount On Months Of The Year Input -->
                                        <discountOnMonthsOfTheYearInput :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></discountOnMonthsOfTheYearInput>

                                    </Col>

                                </Row>

                                <Row :gutter="12">

                                    <Col :span="24">

                                        <!-- Allow Discount On New Customer Checkbox -->
                                        <allowDiscountOnNewCustomerCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnNewCustomerCheckbox>

                                    </Col>

                                    <Col :span="24">

                                        <!-- Allow Discount On Existing Customer Checkbox -->
                                        <allowDiscountOnExistingCustomerCheckbox :couponForm="couponForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDiscountOnExistingCustomerCheckbox>

                                    </Col>

                                </Row>

                                <summaryAlert :couponForm="couponForm" :location="location"></summaryAlert>

                                <!-- If we are editting -->
                                <template v-if="isEditing">

                                    <!-- Save Changes Button -->
                                    <basicButton :disabled="(!couponHasChanged || isSavingChanges)" :loading="isSavingChanges"
                                                 :ripple="(couponHasChanged && !isSavingChanges)" type="success" size="large"
                                                 :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                        <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                                    </basicButton>

                                </template>

                                <!-- If we are creating -->
                                <template v-if="!isEditing">

                                    <!-- Create Button -->
                                    <basicButton :disabled="(!couponHasChanged || isCreating)" :loading="isCreating"
                                                 :ripple="(couponHasChanged && !isCreating)" type="success" size="large"
                                                 :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                        <span>{{ isCreating ? 'Creating...' : 'Create Coupon' }}</span>
                                    </basicButton>

                                </template>


                            </Form>

                        </Col>

                    </Row>

                    <!-- If we are not loading and don't have the coupon -->
                    <Alert v-else-if="(isEditing && !isLoadingCoupon && !localCoupon)" type="warning" class="mx-5" show-icon>
                        Coupon Not Found
                        <template slot="desc">
                        We could not get the coupon, try refreshing your browser. It's also possible that this coupon has been deleted.
                        </template>
                    </Alert>

                </Col>

            </Row>

        </Col>
    </Row>

</template>

<script>

    import nameInput from './components/nameInput.vue';
    import codeInput from './components/codeInput.vue';
    import summaryAlert from './components/summaryAlert.vue';
    import activeSwitch from './components/activeSwitch.vue';
    import fixedRateInput from './components/fixedRateInput.vue';
    import usageLimitInput from './components/usageLimitInput.vue';
    import descriptionInput from './components/descriptionInput.vue';
    import freeDeliveryAlert from './components/freeDeliveryAlert.vue';
    import usageQuantityInput from './components/usageQuantityInput.vue';
    import percentageRateInput from './components/percentageRateInput.vue';
    import discountOnTimesInput from './components/discountOnTimesInput.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import applyDiscountCheckbox from './components/applyDiscountCheckbox.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import allowUsageLimitCheckbox from './components/allowUsageLimitCheckbox.vue';
    import activationTypeSelectInput from './components/activationTypeSelectInput.vue';
    import allowFreeDeliveryCheckbox from './components/allowFreeDeliveryCheckbox.vue';
    import discountOnTotalItemsInput from './components/discountOnTotalItemsInput.vue';
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';
    import discountRateTypeSelectInput from './components/discountRateTypeSelectInput.vue';
    import discountOnMinimumTotalInput from './components/discountOnMinimumTotalInput.vue';
    import allowDiscountOnTimesCheckbox from './components/allowDiscountOnTimesCheckbox.vue';
    import discountOnDaysOfTheWeekInput from './components/discountOnDaysOfTheWeekInput.vue';
    import discountOnDaysOfTheMonthInput from './components/discountOnDaysOfTheMonthInput.vue';
    import discountOnMonthsOfTheYearInput from './components/discountOnMonthsOfTheYearInput.vue';
    import discountOnEndDatetimeLimitInput from './components/discountOnEndDatetimeLimitInput.vue';
    import discountOnTotalUniqueItemsInput from './components/discountOnTotalUniqueItemsInput.vue';
    import discountOnStartDatetimeLimitInput from './components/discountOnStartDatetimeLimitInput.vue';
    import allowDiscountOnTotalItemsCheckbox from './components/allowDiscountOnTotalItemsCheckbox.vue';
    import allowDiscountOnNewCustomerCheckbox from './components/allowDiscountOnNewCustomerCheckbox.vue';
    import allowDiscountOnEndDatetimeCheckbox from './components/allowDiscountOnEndDatetimeCheckbox.vue';
    import allowDiscountOnMinimumTotalCheckbox from './components/allowDiscountOnMinimumTotalCheckbox.vue';
    import allowDiscountOnDaysOfTheWeekCheckbox from './components/allowDiscountOnDaysOfTheWeekCheckbox.vue';
    import allowDiscountOnStartDatetimeCheckbox from './components/allowDiscountOnStartDatetimeCheckbox.vue';
    import allowDiscountOnDaysOfTheMonthCheckbox from './components/allowDiscountOnDaysOfTheMonthCheckbox.vue';
    import allowDiscountOnMonthsOfTheYearCheckbox from './components/allowDiscountOnMonthsOfTheYearCheckbox.vue';
    import allowDiscountOnExistingCustomerCheckbox from './components/allowDiscountOnExistingCustomerCheckbox.vue';
    import allowDiscountOnTotalUniqueItemsCheckbox from './components/allowDiscountOnTotalUniqueItemsCheckbox.vue';

    export default {
        mixins: [miscMixin],
        components: {
            nameInput, codeInput, summaryAlert, activeSwitch, fixedRateInput, usageLimitInput, descriptionInput,
            freeDeliveryAlert, usageQuantityInput, percentageRateInput, discountOnTimesInput, applyDiscountCheckbox, Loader, allowUsageLimitCheckbox,
            activationTypeSelectInput, basicButton, allowFreeDeliveryCheckbox, discountOnTotalItemsInput, discountRateTypeSelectInput,
            discountOnMinimumTotalInput, allowDiscountOnTimesCheckbox, discountOnDaysOfTheWeekInput, discountOnDaysOfTheMonthInput, discountOnMonthsOfTheYearInput, discountOnEndDatetimeLimitInput, discountOnTotalUniqueItemsInput, discountOnStartDatetimeLimitInput,
            allowDiscountOnTotalItemsCheckbox, allowDiscountOnNewCustomerCheckbox, allowDiscountOnEndDatetimeCheckbox, allowDiscountOnMinimumTotalCheckbox, allowDiscountOnDaysOfTheWeekCheckbox,
            allowDiscountOnStartDatetimeCheckbox, allowDiscountOnDaysOfTheMonthCheckbox, allowDiscountOnMonthsOfTheYearCheckbox, allowDiscountOnExistingCustomerCheckbox, allowDiscountOnTotalUniqueItemsCheckbox
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
            coupon: {
                type: Object,
                default: null
            },
            coupons: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            layoutSize: {
                type: String,
                default: 'large'
            },
        },
        data(){
            return {
                couponForm: null,
                localCoupon: null,
                couponFormRules: {

                },
                isCreating: false,
                isSavingChanges: false,
                isLoadingCoupon: false,
                couponBeforeChanges: null,
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  Prepare the coupon
                this.prepareCoupon();

            }
        },
        computed: {
            isLoading(){
                return (
                    this.isLoadingCoupon || this.isCreating || this.isSavingChanges
                );
            },
            couponUrl(){
                if(this.coupon){

                    //  If we have the coupon url via coupon resource
                    return this.coupon['_links']['self']['href'];

                }else{

                    //  If we have the coupon url via route
                    return decodeURIComponent(this.$route.params.coupon_url);

                }
            },
            createCouponUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:coupons'].href
            },
            couponHasChanged(){

                //  Check if the coupon has been modified
                var status = !_.isEqual(this.couponForm, this.couponBeforeChanges);

                return status;

            },
            isEditing(){
                return this.coupon ? true : false;
            }
        },
        methods: {

            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async prepareCoupon(){

                if( this.isEditing ){

                    //  Reset the coupon form
                    this.couponForm = null;

                    //  Fetch the coupon
                    await this.fetchCoupon();

                    //  Notify parent of fetched coupon
                    this.$emit('fetchedCoupon', this.localCoupon);

                }

                //  Set the form details
                this.couponForm = this.getCouponForm();

                //  Save the form before any changes occur
                this.copyCouponBeforeUpdate();

            },
            async fetchCoupon() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingCoupon = true;

                if( this.couponUrl ){

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.couponUrl)
                        .then(({data}) => {

                            //  Get the coupon
                            self.localCoupon = data || null;

                            //  Stop loader
                            self.isLoadingCoupon = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingCoupon = false;

                        });
                }
            },
            getCouponForm(){

                //  Clone the coupon Object (if any) as a new Object
                var form = _.cloneDeep(Object.assign({},
                    //  Set the default form details
                    {
                        //  Coupon Management
                        name: '',
                        description: '',
                        active: true,
                        apply_discount: false,
                        activation_type: 'use code',
                        code: '',
                        allow_free_delivery: false,

                        //  Pricing Management
                        currency: this.locationCurrencyCode,
                        discount_rate_type: 'Percentage',
                        fixed_rate: 0,
                        percentage_rate: 0,

                        allow_discount_on_minimum_total: false,
                        discount_on_minimum_total: 100,
                        allow_discount_on_total_items: false,
                        discount_on_total_items: 2,
                        allow_discount_on_total_unique_items: false,
                        discount_on_total_unique_items: 2,

                        allow_discount_on_start_datetime: false,
                        discount_on_start_datetime: null,

                        allow_discount_on_end_datetime: false,
                        discount_on_end_datetime: null,

                        allow_usage_limit: false,
                        usage_limit: 100,
                        usage_quantity: 0,

                        allow_discount_on_times: false,
                        discount_on_times: [],

                        allow_discount_on_days_of_the_week: false,
                        discount_on_days_of_the_week: [],

                        allow_discount_on_days_of_the_month: false,
                        discount_on_days_of_the_month: [],

                        allow_discount_on_months_of_the_year: false,
                        discount_on_months_of_the_year: [],

                        allow_discount_on_new_customer: false,
                        allow_discount_on_existing_customer: false,

                        location_id: this.location.id,


                    //  Overide the default form details with the provided coupon Object
                    }, this.localCoupon));

                    if( this.localCoupon ){

                        form.active = this.localCoupon.active.status;
                        form.apply_discount = this.localCoupon.apply_discount.status;
                        form.activation_type = this.localCoupon.activation_type.type;
                        form.allow_free_delivery = this.localCoupon.allow_free_delivery.status;
                        form.currency = this.localCoupon.currency.code;
                        form.discount_rate_type = this.localCoupon.discount_rate_type.type;
                        form.allow_discount_on_minimum_total = this.localCoupon.allow_discount_on_minimum_total.status;
                        form.discount_on_minimum_total = this.localCoupon.discount_on_minimum_total.amount;
                        form.allow_discount_on_total_items = this.localCoupon.allow_discount_on_total_items.status;
                        form.allow_discount_on_total_unique_items = this.localCoupon.allow_discount_on_total_unique_items.status;
                        form.allow_discount_on_start_datetime = this.localCoupon.allow_discount_on_start_datetime.status;
                        form.allow_discount_on_end_datetime = this.localCoupon.allow_discount_on_end_datetime.status;
                        form.allow_usage_limit = this.localCoupon.allow_usage_limit.status;

                        form.allow_discount_on_times = this.localCoupon.allow_discount_on_times.status;
                        form.allow_discount_on_days_of_the_week = this.localCoupon.allow_discount_on_days_of_the_week.status;
                        form.allow_discount_on_days_of_the_month = this.localCoupon.allow_discount_on_days_of_the_month.status;
                        form.allow_discount_on_months_of_the_year = this.localCoupon.allow_discount_on_months_of_the_year.status;
                        form.allow_discount_on_new_customer = this.localCoupon.allow_discount_on_new_customer.status;
                        form.allow_discount_on_existing_customer = this.localCoupon.allow_discount_on_existing_customer.status;
                    }

                return form;

            },
            copyCouponBeforeUpdate(){

                //  Clone the coupon before any changes occur
                this.couponBeforeChanges = _.cloneDeep( this.couponForm );

            },
            closeCoupon(){

                //  If we have the coupons
                if( this.coupons.length ){

                    //  Notify parent to show coupons list
                    this.$emit('close');

                }else{

                    /** Note that using router.push() or router.replace() does not allow us to make a
                     *  page refresh when visiting routes. This is undesirable at this moment since our
                     *  parent component contains the <router-view />. When the page does not refresh,
                     *  the <router-view /> is not able to receice the nested components defined in the
                     *  route.js file. This means that we are then not able to render the nested
                     *  components and present them. To counter this issue we must construct the
                     *  href and use "window.location.href" to make a hard page refresh.
                     */

                    var storeUrl = this.store['_links']['self'].href;

                    //  Set the route to view store coupons
                    var route = {
                            name: 'show-store-coupons',
                            params: {
                                store_url: encodeURIComponent(storeUrl)
                            }
                        };

                    //  Contruct the full path url
                    var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                    //  Visit the url
                    window.location.href = href;

                }
            },
            async handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the form
                this.$refs['couponForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  If we are editing
                        if( this.isEditing ){

                            //  Attempt to save coupon
                            this.saveCoupon();

                        //  If we are creating
                        }else{

                            //  Attempt to create coupon
                            this.createCoupon();

                        }

                    //  If the validation failed
                    } else {

                        //  If we are editing
                        if( this.isEditing ){

                            this.$Message.warning({
                                content: 'Sorry, you cannot update coupon yet',
                                duration: 6
                            });

                        //  If we are creating
                        }else{

                            this.$Message.warning({
                                content: 'Sorry, you cannot create coupon yet',
                                duration: 6
                            });

                        }

                    }
                });

            },
            saveCoupon() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                /** Make an Api call to create the coupon. We include the
                 *  coupon details required for a new coupon creation.
                 */
                let data = {
                    postData: this.couponForm,
                };

                api.call('put', this.couponUrl, data, this)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Instant cart updated success message
                        self.$Message.success({
                            content: 'Your coupon has been updated!',
                            duration: 6
                        });

                        self.copyCouponBeforeUpdate();

                        //  Notify parent on changes
                        self.$emit('savedCoupon', data);

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot update coupon',
                            duration: 6
                        });

                        //  Stop loader
                        self.isSavingChanges = false;

                });
            },
            createCoupon() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                /** Make an Api call to create the coupon. We include the
                 *  coupon details required for a new coupon creation.
                 */
                let data = {
                        postData: this.couponForm
                    };

                api.call('post', this.createCouponUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent of the coupon created
                        self.$emit('createdCoupon', data);

                        //  Instant cart created success message
                        self.$Message.success({
                            content: 'Your coupon has been created!',
                            duration: 6
                        });

                        //  resetForm() declared in miscMixin
                        self.resetForm('couponForm');

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot create coupon',
                            duration: 6
                        });

                        //  Stop loader
                        self.isCreating = false;

                });
            },
        },
        created(){

            //  Prepare the coupon
            this.prepareCoupon();

        }
    };

</script>