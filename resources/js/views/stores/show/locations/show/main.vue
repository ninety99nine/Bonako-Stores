<template>

    <Row>

        <Col :span="12" :offset="6" class="mt-5">
                    
            <!-- If we are loading, Show Loader -->
            <Loader v-if="(isEditing && isLoadingLocation)" class="mb-2">Searching location</Loader>

            <Form v-if="locationForm" ref="locationForm" :model="locationForm" :rules="locationFormRules" class="location-form">

                <template v-show="visibleSections.includes('location-details')">

                    <!-- Heading -->
                    <Divider orientation="left" class="font-weight-bold">Location Details</Divider>

                    <Card>

                        <div :style="onlineStatusStyle" :class="onlineStatusClass">

                            <!-- Toggle Online Switch -->
                            <onlineSwitch :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></onlineSwitch>

                            <!-- If we are offline -->
                            <template v-if="!locationForm.online">

                                <!-- Enter Offline Message Input -->
                                <offlineMessageTextarea :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></offlineMessageTextarea>

                            </template>

                        </div>

                        <!-- Enter Name Input -->
                        <nameInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></nameInput>

                        <!-- Enter Call To Action Input -->
                        <callToActionInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></callToActionInput>

                    </Card>

                    <!-- Heading -->
                    <Divider orientation="left" :class="['font-weight-bold', 'mb-3', 'mt-4']">Delivery Details</Divider>

                    <Card>
                        
                        <!-- Toggle Allow Delivery Switch -->
                        <allowDeliverySwitch :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowDeliverySwitch>

                        <!-- If we allow delivery -->
                        <template v-if="locationForm.allow_delivery">

                            <!-- Enter Delivery Note Input -->
                            <deliveryNoteTextarea :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></deliveryNoteTextarea>

                            <Row class="mt-2">

                                <Col :span="12">

                                    <!-- Enter Delivery Flat Fee Input -->
                                    <deliveryFlatFeeInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></deliveryFlatFeeInput>

                                </Col>

                                <Col :span="12">

                                    <!-- Free delivery Checkbox -->
                                    <allowFreeDeliveryCheckbox :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowFreeDeliveryCheckbox>

                                </Col>

                            </Row>

                            <!-- Disclaimer: Free Delivery -->
                            <freeDeliveryAlert :location="locationForm"></freeDeliveryAlert>

                            <!-- Disclaimer: No Price -->
                            <flatFeeDeliveryAlert :location="locationForm" :currencySymbol="locationCurrencySymbol"></flatFeeDeliveryAlert>

                            <!-- Delivery Destinations Select -->
                            <deliveryDestinationsSelectInput :locationForm="locationForm" :currencySymbol="locationCurrencySymbol" 
                                                             :isLoading="isLoading" :serverErrors="serverErrors" :formatPrice="formatPrice">
                            </deliveryDestinationsSelectInput>

                            <!-- Delivery Days Select -->
                            <deliveryDaysSelectInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></deliveryDaysSelectInput>

                            <!-- Delivery Times Select -->
                            <deliveryTimesSelectInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></deliveryTimesSelectInput>

                        </template>
                        
                    </Card>

                    <!-- Heading -->
                    <Divider orientation="left" :class="['font-weight-bold', 'mb-3', 'mt-4']">Pickup Details</Divider>

                    <Card>
                        
                        <!-- Toggle Allow Pickup Switch -->
                        <allowPickupSwitch :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowPickupSwitch>

                        <!-- If we allow pickup -->
                        <template v-if="locationForm.allow_pickups">

                            <!-- Enter Pickup Note Input -->
                            <pickupNoteTextarea :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></pickupNoteTextarea>

                            <!-- Pickup Destinations Select -->
                            <pickupDestinationsSelectInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></pickupDestinationsSelectInput>

                            <!-- Pickup Days Select -->
                            <pickupDaysSelectInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></pickupDaysSelectInput>

                            <!-- Pickup Times Select -->
                            <pickupTimesSelectInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></pickupTimesSelectInput>

                        </template>

                    </Card>

                    <!-- Heading -->
                    <Divider orientation="left" :class="['font-weight-bold', 'mb-3', 'mt-4']">Payment Details</Divider>

                    <Card>
                        
                        <!-- Toggle Allow Payments Switch -->
                        <allowPaymentsSwitch :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowPaymentsSwitch>

                        <!-- If we allow payments -->
                        <template v-if="locationForm.allow_payments">

                            <!-- Pickup Days Select -->
                            <paymentSelectInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></paymentSelectInput>

                            <!-- Orange Money Merchant Code Input -->
                            <orangeMoneyMerchantCodeInput :locationForm="locationForm" :isLoading="isLoading" :serverErrors="serverErrors"></orangeMoneyMerchantCodeInput>

                        </template>

                    </Card>

                    <!-- Save Changes Button -->
                    <div :class="['clearfix', 'mt-2']">

                        <basicButton :disabled="(!locationHasChanged || isLoading)" :loading="isSavingChanges"
                                     :ripple="(locationHasChanged && !isLoading)" type="success" size="large"
                                     class="float-right" @click.native="handleSubmit()">
                            <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                        </basicButton>

                    </div>
                
                </template>

            </Form>

        </Col>

    </Row>

</template>
<script>

    import nameInput from './components/nameInput.vue';
    import onlineSwitch from './components/onlineSwitch.vue';
    import freeDeliveryAlert from './components/freeDeliveryAlert.vue';
    import allowPickupSwitch from './components/allowPickupSwitch.vue';
    import callToActionInput from './components/callToActionInput.vue';
    import paymentSelectInput from './components/paymentSelectInput.vue';
    import pickupNoteTextarea from './components/pickupNoteTextarea.vue';
    import allowPaymentsSwitch from './components/allowPaymentsSwitch.vue';
    import allowDeliverySwitch from './components/allowDeliverySwitch.vue';
    import deliveryNoteTextarea from './components/deliveryNoteTextarea.vue';
    import deliveryFlatFeeInput from './components/deliveryFlatFeeInput.vue';
    import flatFeeDeliveryAlert from './components/flatFeeDeliveryAlert.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import pickupDaysSelectInput from './components/pickupDaysSelectInput.vue';
    import pickupTimesSelectInput from './components/pickupTimesSelectInput.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import offlineMessageTextarea from './components/offlineMessageTextarea.vue';
    import deliveryDaysSelectInput from './components/deliveryDaysSelectInput.vue';
    import deliveryTimesSelectInput from './components/deliveryTimesSelectInput.vue';
    import allowFreeDeliveryCheckbox from './components/allowFreeDeliveryCheckbox.vue';
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';
    import orangeMoneyMerchantCodeInput from './components/orangeMoneyMerchantCodeInput.vue';
    import pickupDestinationsSelectInput from './components/pickupDestinationsSelectInput.vue';
    import deliveryDestinationsSelectInput from './components/deliveryDestinationsSelectInput.vue';

    export default {
        mixins: [miscMixin],
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            visibleSections: {
                type: Array,
                default: function(){
                    return ['location-details'];
                }
            },
        },
        components: { 
            nameInput, onlineSwitch, freeDeliveryAlert, allowPickupSwitch, callToActionInput, paymentSelectInput, 
            pickupNoteTextarea, allowPaymentsSwitch, allowDeliverySwitch, deliveryNoteTextarea, deliveryFlatFeeInput, 
            flatFeeDeliveryAlert, allowFreeDeliveryCheckbox, Loader, pickupDaysSelectInput, pickupTimesSelectInput, 
            offlineMessageTextarea, deliveryDaysSelectInput, deliveryTimesSelectInput, orangeMoneyMerchantCodeInput,
            basicButton, pickupDestinationsSelectInput, deliveryDestinationsSelectInput
        },
        data () {
            return {
                locationForm: null,
                localLocation: null,
                isSavingChanges: false,
                isLoadingLocation: false,
                locationBeforeChanges: null,
                locationFormRules: {
                    name: [
                        { required: true, message: 'Please enter your location name', trigger: 'blur' },
                        { min: 3, message: 'Location name is too short', trigger: 'change' },
                        { max: 30, message: 'Location name is too long', trigger: 'change' }
                    ],
                    call_to_action: [
                        { required: true, message: 'Please enter your call to action e.g Buy Grocery', trigger: 'blur' },
                        { min: 3, message: 'Call to action is too short', trigger: 'change' },
                        { max: 20, message: 'Call to action is too long', trigger: 'change' }
                    ],
                    offline_message: [
                        { min: 3, message: 'Offline message is too short', trigger: 'change' },
                        { max: 140, message: 'Offline message is too long', trigger: 'change' }
                    ],
                    delivery_note: [
                        { min: 3, message: 'Delivery notice is too short', trigger: 'change' },
                        { max: 140, message: 'Delivery notice is too long', trigger: 'change' }
                    ],
                    pickup_note: [
                        { min: 3, message: 'Pickup notice is too short', trigger: 'change' },
                        { max: 140, message: 'Pickup notice is too long', trigger: 'change' }
                    ]
                }
            }
        },
        computed: {
            isEditing(){
                return this.location ? true : false;
            },
            isLoading(){
                return this.isLoadingLocation || this.isSavingChanges;
            },
            locationUrl(){
                return this.location['_links']['self'].href
            },
            locationHasChanged(){

                //  Check if the location has been modified
                var status = !_.isEqual(this.locationForm, this.locationBeforeChanges);

                return status;

            },
            onlineStatusStyle(){
                return this.locationForm.online ? {} : { 
                    background: 'floralwhite' 
                };
            },
            onlineStatusClass(){
                return this.locationForm.online ? {} : ['p-2', 'mb-3', 'rounded'];
            },
            locationCurrency(){
                return (this.location.currency || {
                    code: 'BWP',
                    symbol: 'P'
                });
            },
            locationCurrencySymbol(){
                return this.locationCurrency.symbol;
            },
        },
        methods: {

            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async prepareLocation(){

                if( this.isEditing ){

                    //  Reset tthe location form
                    this.locationForm = null;

                    //  Fetch the location
                    await this.fetchLocation();

                    //  Notify parent of fetched location
                    this.$emit('updatedLocation', this.localLocation);

                }

                //  Set the form details
                this.locationForm = this.getLocationForm();

                //  Save the form before any changes occur
                this.copyLocationBeforeUpdate();

            },
            async fetchLocation() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                if( this.locationUrl ){

                    //  Start loader
                    self.isLoadingLocation = true;

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.locationUrl)
                        .then(({data}) => {

                            //  Get the location
                            self.localLocation = data || null;

                            //  Stop loader
                            self.isLoadingLocation = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingLocation = false;

                        });
                }
            },
            getLocationForm(){

                var form = Object.assign({},
                    //  Set the default form details
                    {
                        name: '',
                        online: true,
                        about_us: '',
                        contact_us: '',
                        call_to_action: '',
                        currency: 'BWP',
                        offline_message: 'Sorry, we are currently offline',

                        //  Delivery Details
                        allow_delivery: false,
                        delivery_note: '',
                        delivery_flat_fee: 0,
                        allow_free_delivery: false,
                        delivery_destinations: null,
                        delivery_days: null,
                        delivery_times: null,

                        //  Pickup Details
                        allow_pickups: false,
                        pickup_note: '',
                        pickup_destinations: null,
                        pickup_days: null,
                        pickup_times: null,

                        //  Payment Details
                        allow_payments: false,
                        online_payment_methods: null,
                        offline_payment_methods: null,
                        orange_money_merchant_code: null,

                        allow_sending_merchant_sms: false

                    //  Overide the default form details with the provided location details
                    }, this.location);

                //  If we are editing an existing location
                if( this.isEditing ){

                    console.log('form 1');
                    console.log(form);

                    //  Correct the form using the location details
                    form.online = this.location.online.status;
                    form.allow_delivery = this.location.allow_delivery.status;
                    form.allow_free_delivery = this.location.allow_free_delivery.status;
                    form.allow_pickups = this.location.allow_pickups.status;
                    form.allow_payments = this.location.allow_payments.status;
                    form.allow_sending_merchant_sms = this.location.allow_sending_merchant_sms.status;
                    
                    form.delivery_flat_fee = this.location.delivery_flat_fee.amount;
                    form.currency = this.location.currency.code;


                    console.log('form 2');
                    console.log(form);

                }

                return form;

            },
            /**
             *  We use the $nextTick() method to wait for the DOM to render the
             *  "deliveryDestinationsSelectInput" component since it updates the
             *  the "locationForm". The copyLocationBeforeUpdate() will get the
             *  "locationForm" before the DOM is updated resulting in an older
             *  version of the "locationForm" since it then gets updated by
             *  the "deliveryDestinationsSelectInput" component.
             */
            copyLocationBeforeUpdate(){

                //  DOM is not updated yet
                this.$nextTick(function () {

                    //  DOM is updated

                    //  Clone the location before any changes occur
                    this.locationBeforeChanges = _.cloneDeep( this.locationForm );

                });

            },
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the location form
                this.$refs['locationForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  Attempt to create location
                        this.saveLocation();

                    //  If the validation failed
                    } else {
                        this.$Message.warning({
                            content: 'Sorry, you cannot update yet',
                            duration: 6
                        });
                    }
                })
            },
            saveLocation() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                console.log('saveLocation()');

                if( this.locationUrl ){

                    console.log('this.locationUrl');
                    console.log(this.locationUrl);

                    //  Start loader
                    self.isSavingChanges = true;

                    /** Make an Api call to create the location. We include the
                     *  location details required for a new location creation.
                     */
                    let data = {
                        postData: this.locationForm
                    };

                    console.log('data');
                    console.log(data);

                api.call('put', this.locationUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        self.$emit('isSaving', self.isSavingChanges);

                        self.$emit('selectedLocation', data);

                        //  Location updated success message
                        self.$Message.success({
                            content: 'Your location has been updated!',
                            duration: 6
                        });

                        self.copyLocationBeforeUpdate();

                        self.notifyUnsavedChangesStatus();

                    }).catch((response) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                });
                }
            }
        },
        created(){

            //  Prepare the location
            this.prepareLocation();

        }
    }
</script>
