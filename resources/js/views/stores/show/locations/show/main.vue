<template>

    <Row>

        <Col :span="12" :offset="6">

            <Card class="mt-3 pt-2">
                
                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold">Location Details</Divider>

                <!-- Server Error Message Alert -->
                <Alert v-if="serverErrorMessage && !isSavingChanges" type="warning">{{ serverErrorMessage }}</Alert>

                <!-- If we are loading, Show Loader -->
                <Loader v-show="isLoadingLocation" class="mt-2">Loading location...</Loader>

                <Form v-if="!isLoadingLocation && locationForm" ref="locationForm" :model="locationForm" :rules="locationFormRules">
                    
                    <div :class="[locationForm.online ? '' : 'bg-grey-light rounded pt-2 px-3']">

                        <!-- Set Online Status -->
                        <FormItem prop="online" :error="serverOnlineError" class="mb-2">
                            <div>
                                <span :style="{ width: '200px' }" class="font-weight-bold">{{ statusText }}: </span>
                                <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300" 
                                        content="Turn on to allow subscribers to access this store location">
                                    <i-Switch v-model="locationForm.online" />
                                </Poptip>
                            </div>
                        </FormItem>
                        
                        <template v-if="!locationForm.online">

                            <!-- Set Offline Status Message -->
                            <FormItem prop="offline_message" :error="serverOfflineMessageError" class="mb-2">
                                <div class="d-flex">
                                    <span :style="{ width: '150px' }">Offline Message: </span>
                                    <Input type="textarea" v-model="locationForm.offline_message" placeholder="Enter offline message" :disabled="isSavingChanges" 
                                            maxlength="160" show-word-limit @keyup.enter.native="handleSubmit()">
                                    </Input>
                                </div>
                            </FormItem>

                            <Divider class="mb-2"></Divider>

                        </template>

                    </div>

                    <!-- Enter Name -->
                    <FormItem label="Name" prop="name" :error="serverNameError" class="mb-2">
                        <Input type="text" v-model="locationForm.name" placeholder="Name" :disabled="isSavingChanges" 
                                maxlength="50" show-word-limit @keyup.enter.native="handleSubmit()">
                        </Input>
                    </FormItem>

                    <!-- Call To Action -->
                    <FormItem label="Call To Action" prop="call_to_action" :error="serverCallToActionError" class="mb-2">
                        <Input type="text" v-model="locationForm.call_to_action" placeholder="Call to action e.g Buy Grocery" :disabled="isSavingChanges" 
                                maxlength="50" show-word-limit @keyup.enter.native="handleSubmit()">
                        </Input>
                    </FormItem>

                    <!-- Enter About Us -->
                    <FormItem label="About Us" prop="about_us" :error="serverAboutUsError" class="mb-2">
                        <Input type="textarea" v-model="locationForm.about_us" placeholder="Describe this store location and its offerings" :disabled="isSavingChanges" 
                                maxlength="140" show-word-limit @keyup.enter.native="handleSubmit()">
                        </Input>
                    </FormItem>

                    <!-- Enter Contact Us -->
                    <FormItem label="Contact Us" prop="contact_us" :error="serverContactUsError" class="mb-2">
                        <Input type="textarea" v-model="locationForm.contact_us" placeholder="Enter contact information for this store location e.g phone number, email e.t.c" :disabled="isSavingChanges" 
                                maxlength="140" show-word-limit @keyup.enter.native="handleSubmit()">
                        </Input>
                    </FormItem>
                
                    <!-- Heading -->
                    <Divider orientation="left" class="font-weight-bold">Delivery Details</Divider>
                        
                    <!-- Allow Delivery -->
                    <FormItem prop="allow_delivery" class="mb-2">
                        <div>
                            <span :style="{ width: '200px' }" class="font-weight-bold">Allow Delivery: </span>
                            <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300" 
                                    content="Turn on to allow delivery for orders placed to this location">
                                <i-Switch v-model="locationForm.allow_delivery" />
                            </Poptip>
                        </div>
                    </FormItem>

                    <template v-if="locationForm.allow_delivery">
                        
                        <!-- Set Delivery Policy Message -->
                        <FormItem prop="delivery_note" :error="serverDeliveryNoteError" class="mb-2">
                            <div class="d-flex">
                                <span :style="{ width: '160px' }">Delivery Notice: </span>
                                <Input type="textarea" v-model="locationForm.delivery_note" placeholder="Enter delivery notice or announcement" :disabled="isSavingChanges" 
                                        maxlength="160" show-word-limit @keyup.enter.native="handleSubmit()">
                                </Input>
                            </div>
                        </FormItem>
                        
                        <!-- Set Delivery Flat Fee -->
                        <FormItem prop="delivery_flat_fee" class="mb-2">
                            <div class="d-flex">
                                <span :style="{ width: '160px' }">Delivery Flat Fee: </span>
                                <Poptip trigger="hover" content="This is a flat fee charged for delivery to any destination" word-wrap class="poptip-w-100">
                                    <InputNumber v-model="locationForm.delivery_flat_fee" :disabled="isSavingChanges" 
                                                 placeholder="40" class="w-100" @keyup.enter.native="handleSubmit()">
                                    </InputNumber>
                                </Poptip>
                            </div>

                            <!-- Flat Fee Desclaimer-->
                            <Alert v-if="locationForm.delivery_flat_fee != null" type="info" class="my-2">
                                <span class="font-weight-bold">Note:</span>
                                <span v-if="locationForm.delivery_flat_fee == 0">Delivery to any destination will be <span class="font-weight-bold text-success">Free Delivery</span></span>
                                <span v-else>Delivery to any destination will be charged {{ store.currency.symbol + locationForm.delivery_flat_fee }}</span>
                            </Alert>
                            
                        </FormItem>
                        
                        <!-- Delivery Destinations -->
                        <div class="d-flex mb-2">
                            <span :style="{ width: '160px' }">Destinations: </span>
                            <Poptip trigger="hover" content="Which destinations do you deliver orders" word-wrap class="poptip-w-100">
                                <Select v-model="deliveryDestinationNames" filterable multiple allow-create 
                                        @on-create="addDeliveryDestination($event, null, locationForm.delivery_destinations)" class="w-100">
                                    <Option v-for="(destination, index) in deliveryDestinations" :label="destination.name" 
                                            :value="destination.name" :key="index">
                                        <span>{{ destination.name }}</span>
                                        <span :style="{ color:'#ccc' }" class="float-right mr-4">
                                            Cost: {{ destination.cost ? store.currency.symbol + destination.cost : 'None' }}
                                        </span>
                                    </Option>
                                </Select>
                            </Poptip>
                        </div>
                        <div v-if="locationForm.delivery_destinations.length" class="clearfix mb-2">
                            <!-- Manage Pricing Button -->
                            <Button type="primary" size="small" class="float-right"
                                    @click.native="isOpenManageDestinationsModal = true">
                                <Icon type="ios-pin-outline" :size="20" class="mr-1"/>
                                <span>Destination Pricing</span>
                            </Button>
                        </div>
                        
                        <!-- Delivery Days -->
                        <div class="d-flex mb-2">
                            <span :style="{ width: '160px' }">Delivery Days: </span>
                            <Poptip trigger="hover" content="Which days do you deliver orders" word-wrap class="poptip-w-100">
                                <Select v-model="locationForm.delivery_days" filterable multiple class="w-100">
                                    <Option v-for="(day, index) in deliveryDays" :value="day" :key="index">
                                        {{ day }}
                                    </Option>
                                </Select>
                            </Poptip>
                        </div>
                        
                        <!-- Delivery Times -->
                        <div class="d-flex mb-2">
                            <span :style="{ width: '160px' }">Delivery Times: </span>
                            <Poptip trigger="hover" content="Which times do you deliver orders" word-wrap class="poptip-w-100">
                                <Select v-model="locationForm.delivery_times" filterable multiple allow-create 
                                        @on-create="addDeliveryTime($event, locationForm.delivery_times)" class="w-100">
                                    <Option v-for="(day, index) in deliveryTimes" :value="day" :key="index">
                                        {{ day }}
                                    </Option>
                                </Select>
                            </Poptip>
                        </div>

                    </template>
                    
                    <!-- Allow Pickups -->
                    <FormItem prop="allow_pickups" class="mb-2">
                        <div>
                            <span class="font-weight-bold mr-2">Allow Pickups: </span>
                            <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300" 
                                    content="Turn on to allow customers to pickup their own orders">
                                <i-Switch v-model="locationForm.allow_pickups" />
                            </Poptip>
                        </div>
                    </FormItem>

                    <template v-if="locationForm.allow_pickups">
                    
                        <!-- Set Pickup Policy Message -->
                        <FormItem prop="pickup_note" :error="serverPickupNoteError" class="mb-2">
                            <div class="d-flex">
                                <span :style="{ width: '160px' }">Pickup Notice: </span>
                                <Input type="textarea" v-model="locationForm.pickup_note" placeholder="Enter pickup notice or announcement" :disabled="isSavingChanges" 
                                        maxlength="160" show-word-limit @keyup.enter.native="handleSubmit()">
                                </Input>
                            </div>
                        </FormItem>
                    
                        <!-- Pickup Destinations -->
                        <div class="d-flex mb-2">
                            <span :style="{ width: '180px' }" class="mt-1">Pickup Destinations: </span>
                            <Poptip trigger="hover" content="Which destinations do you allow customers to pickup their orders" word-wrap class="poptip-w-100">
                                <Select v-model="locationForm.pickup_destinations" filterable multiple allow-create 
                                        @on-create="addPickupDestination($event, locationForm.pickup_destinations)" class="w-100">
                                    <Option v-for="(destination, index) in pickupDestinations" :value="destination" :key="index">
                                        {{ destination }}
                                    </Option>
                                </Select>
                            </Poptip>
                        </div>
                        
                        <!-- Pickup Days -->
                        <div class="d-flex mb-2">
                            <span :style="{ width: '180px' }">Pickup Days: </span>
                            <Poptip trigger="hover" content="Which days do you allow customers to pickup their orders" word-wrap class="poptip-w-100">
                                <Select v-model="locationForm.pickup_days" filterable multiple class="w-100">
                                    <Option v-for="(day, index) in pickupDays" :value="day" :key="index">
                                        {{ day }}
                                    </Option>
                                </Select>
                            </Poptip>
                        </div>
                        
                        <!-- Pickup Times -->
                        <div class="d-flex mb-2">
                            <span :style="{ width: '180px' }">Pickup Times: </span>
                            <Poptip trigger="hover" content="Which times do you allow customers to pickup their orders" word-wrap class="poptip-w-100">
                                <Select v-model="locationForm.pickup_times" filterable multiple allow-create 
                                    @on-create="addPickupTime($event, locationForm.pickup_times)" class="w-100">
                                    <Option v-for="(day, index) in pickupTimes" :value="day" :key="index">
                                        {{ day }}
                                    </Option>
                                </Select>
                            </Poptip>
                        </div>
                    
                    </template>
                
                    <!-- Heading -->
                    <Divider orientation="left" class="font-weight-bold">Payment Details</Divider>
                    
                    <!-- Allow Payments -->
                    <FormItem prop="allow_payments" class="mb-2">
                        <div>
                            <span :style="{ width: '200px' }" class="font-weight-bold">Allow Payments: </span>
                            <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300" 
                                    content="Turn on to allow payments for orders placed to this location">
                                <i-Switch v-model="locationForm.allow_payments" />
                            </Poptip>
                        </div>
                    </FormItem>

                    <template v-if="locationForm.allow_payments">

                        <!-- Supported Online Payment Methods -->
                        <FormItem prop="online_payment_methods" class="mb-2">  
                            <div class="d-flex">
                                <span :style="{ width: '180px' }">Online Payments: </span>  
                                <Select v-model="locationForm.online_payment_methods" multiple :disabled="isLoadingPaymentMethods" class="w-100 mr-2">
                                    <Option v-for="(paymentMethod, index) in onlinePaymentMethods" :value="paymentMethod.name" :key="index">
                                        {{ paymentMethod.name }}
                                    </Option>
                                </Select>
                                <!-- Refresh Button -->
                                <Poptip trigger="hover" content="Refresh the payment methods" word-wrap width="300"
                                        :style="{ marginTop: '-2px' }">
                                    <Button class="p-1" @click.native="fetchPaymentMethods()">
                                        <Icon type="ios-refresh" :size="20" />
                                    </Button>
                                </Poptip>
                            </div>
                        </FormItem>

                        <!-- Supported offline Payment Methods -->
                        <FormItem prop="offline_payment_methods" class="mb-2">  
                            <div class="d-flex">
                                <span :style="{ width: '180px' }">Offline Payments: </span>  
                                <Select v-model="locationForm.offline_payment_methods" multiple :disabled="isLoadingPaymentMethods" class="w-100 mr-2">
                                    <Option v-for="(paymentMethod, index) in offlinePaymentMethods" :value="paymentMethod.name" :key="index">
                                        {{ paymentMethod.name }}
                                    </Option>
                                </Select>
                                <!-- Refresh Button -->
                                <Poptip trigger="hover" content="Refresh the payment methods" word-wrap width="300"
                                        :style="{ marginTop: '-2px' }">
                                    <Button class="p-1" @click.native="fetchPaymentMethods()">
                                        <Icon type="ios-refresh" :size="20" />
                                    </Button>
                                </Poptip>
                            </div>
                        </FormItem>
                        
                    </template>
                    
                    <!-- Save Changes Button -->
                    <FormItem v-if="!isSavingChanges">

                        <basicButton :disabled="(!locationHasChanged || isSavingChanges)" :loading="isSavingChanges" 
                                     :ripple="(locationHasChanged && !isSavingChanges)" type="success" size="large" 
                                     class="float-right" @click.native="handleSubmit()">
                            <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                        </basicButton>

                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isSavingChanges" class="mt-2">Saving location...</Loader>

                </Form>

            </Card>
        </Col>

        <!-- 
            MODAL EDIT LOCATION DESTINATIONS
        -->
        <template v-if="isOpenManageDestinationsModal">

            <manageDestinationsModal
                :store="store"
                :location="locationForm"
                @updated="handleUpdatedDestinations($event)"
                @visibility="isOpenManageDestinationsModal = $event">
            </manageDestinationsModal>

        </template>
        
    </Row>

</template>
<script>
    
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import manageDestinationsModal from './manageDestinationsModal';

    export default {
        props: {
            store: {
                type: Object,
                default: null
            },
            requestToSaveChanges: {
                type: Number,
                default: 0
            }
        },
        components: { basicButton, Loader, manageDestinationsModal },
        data () {

            return {
                location: null,
                locationForm: null,
                paymentMethods: [],
                isSavingChanges: false,
                isLoadingLocation: false,
                locationHasChanged: false,
                locationBeforeChanges: null,
                isLoadingPaymentMethods: false,
                isOpenManageDestinationsModal: false,
                deliveryDestinations: [],
                deliveryDays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                deliveryTimes: [
                    '6am', '7am', '8am', '9am', '10am', '11am', '12pm', 
                    '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm'
                ],       
                pickupDestinations: [],         
                pickupDays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],         
                pickupTimes: [
                    '6am', '7am', '8am', '9am', '10am', '11am', '12pm', 
                    '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm'
                ],        
                locationFormRules: {
                    name: [
                        { required: true, message: 'Please enter your location name', trigger: 'blur' },
                        { min: 3, message: 'Location name is too short', trigger: 'change' },
                        { max: 50, message: 'Location name is too long', trigger: 'change' }
                    ],
                    call_to_action: [
                        { required: true, message: 'Please enter your call to action e.g Buy Grocery', trigger: 'blur' },
                        { min: 3, message: 'Call to action is too short', trigger: 'change' },
                        { max: 140, message: 'Call to action is too long', trigger: 'change' }
                    ],
                    about_us: [
                        { required: true, message: 'Please enter location details', trigger: 'blur' },
                        { min: 3, message: 'About us information is too short', trigger: 'change' },
                        { max: 140, message: 'About us information is too long', trigger: 'change' }
                    ],
                    contact_us: [
                        { required: true, message: 'Please enter location contact details', trigger: 'blur' },
                        { min: 3, message: 'Contact us information is too short', trigger: 'change' },
                        { max: 140, message: 'Contact us information is too long', trigger: 'change' }
                    ],
                    offline_message: [
                        { min: 3, message: 'Offline message is too short', trigger: 'change' },
                        { max: 160, message: 'Offline message is too long', trigger: 'change' }
                    ],
                    delivery_note: [
                        { min: 3, message: 'Delivery notice is too short', trigger: 'change' },
                        { max: 140, message: 'Delivery notice is too long', trigger: 'change' }
                    ],
                    pickup_note: [
                        { min: 3, message: 'Pickup notice is too short', trigger: 'change' },
                        { max: 140, message: 'Pickup notice is too long', trigger: 'change' }
                    ]

                    
                },
                serverErrors: [],
                serverErrorMessage: ''
            }
        },
        watch: {
            /** Keep track of changes on the location
             *  This could include location changes without a hard page refresh
             */
            location: {

                handler: function (val, oldVal) {

                    //  Reset everything
                    this.setFormAndCaptureBeforeChanges();

                },
                deep: true

            },
            /** Keep track of changes on the location form
             *  This includes changes as we edit the form details
             */
            locationForm: {

                handler: function (val, oldVal) {

                    this.notifyUnsavedChangesStatus();

                },
                deep: true

            },
            /** Watch to see if we want to save changes.
             *  If we do handle the request.
             */
            requestToSaveChanges(newVal, oldVal){
                this.handleSubmit();
            }
        },
        computed: {
            serverNameError(){
                return (this.serverErrors || {}).name;
            },
            serverOnlineError(){
                return (this.serverErrors || {}).online;
            },
            serverOfflineMessageError(){
                return (this.serverErrors || {}).offline_message;
            },
            serverCallToActionError(){
                return (this.serverErrors || {}).call_to_action;
            },
            serverAboutUsError(){
                return (this.serverErrors || {}).about_us;
            },
            serverContactUsError(){
                return (this.serverErrors || {}).contact_us;
            },
            serverDeliveryNoteError(){
                return (this.serverErrors || {}).delivery_note;
            },
            serverPickupNoteError(){
                return (this.serverErrors || {}).pickup_note;
            },
            statusText(){
                return this.locationForm.online ? 'Online' : 'Offline'
            },
            locationUrl(){
                return decodeURIComponent(this.$route.params.location_url);
            },
            onlinePaymentMethods(){
                return this.paymentMethods.filter((paymentMethod, index) => {
                    return paymentMethod.used_online;
                });
            },
            offlinePaymentMethods(){
                return this.paymentMethods.filter((paymentMethod, index) => {
                    return paymentMethod.used_offline;
                });
            },
            deliveryDestinationNames: {
                get: function () {

                    //  Return the destination names only
                    return this.locationForm.delivery_destinations.map((destination, index) => {
                        return destination.name;
                    });

                },
                set: function (names) {

                    var newDestinations = [];

                    //  Get details of the selected destinations
                    var selectedDestinations = names.map((name, index) => {

                        //  Check if we have any destination already existing with this name
                        for (let i = 0; i < this.deliveryDestinations.length; i++) {

                            //  If we found a destination that matches the given name
                            if( this.deliveryDestinations[i].name == name ){

                                //  Return this already existing destination information
                                return this.deliveryDestinations[i];

                            }
                        }

                        //  Construct details of this new destination
                        var newDestination =  {
                            name: name,
                            cost: null
                        };

                        console.log('this.deliveryDestinations before push');
                        console.log(this.deliveryDestinations);

                        //  Update the delivery destination with this new destination
                        this.deliveryDestinations.push(newDestination);

                        //  Return this new destination information
                        return {
                            name: name,
                            cost: null
                        };

                    });

                    //  Update the location delivery details with the selected destinations
                    this.$set(this.locationForm, 'delivery_destinations', selectedDestinations);
                }
            }
        },
        methods: {
            handleUpdatedDestinations(updatedDestinations){

                //  Update the location destinations
                this.deliveryDestinations = updatedDestinations;
                this.$set(this.locationForm, 'delivery_destinations', updatedDestinations);

            },
            addDeliveryDestination(name, cost, target){

                //  If the delivery destination is set to null
                if( target == null ){

                    //  Convert it into an Array
                    target = [];

                }

                //  Check if the destination already exists
                var alreadyExists = target.filter((currDestination, index) => {
                    return currDestination.name == name;
                }).length ? true : false;

                //  If the destination already exists
                if( !alreadyExists ){

                    //  Add the destination 
                    target.push({
                        name: name,
                        cost: cost
                    });

                }
            },
            addDeliveryTime(time, target){

                //  If the delivery times are set to null
                if( target == null ){

                    //  Convert it into an Array
                    target = [];

                }

                //  Check if the time already exists
                var alreadyExists = target.filter((currTime, index) => {
                    return currTime == time;
                }).length ? true : false;

                //  If the time already exists
                if( !alreadyExists ){

                    //  Add the time 
                    target.push(time);

                }
            },
            addPickupDestination(destination, target){

                //  If the pickup destination is set to null
                if( target == null ){

                    //  Convert it into an Array
                    target = [];

                }

                //  Check if the destination already exists
                var alreadyExists = target.filter((currDestination, index) => {
                    return currDestination == destination;
                }).length ? true : false;

                //  If the destination already exists
                if( !alreadyExists ){

                    //  Add the destination 
                    target.push(destination);

                }
            },
            addPickupTime(time, target){

                //  If the pickup times are set to null
                if( target == null ){

                    //  Convert it into an Array
                    target = [];

                }

                //  Check if the time already exists
                var alreadyExists = target.filter((currTime, index) => {
                    return currTime == time;
                }).length ? true : false;

                //  If the time already exists
                if( !alreadyExists ){

                    //  Add the time 
                    target.push(time);

                }
            },
            setFormAndCaptureBeforeChanges(){
                this.locationForm = this.getLocationForm();
                this.copyLocationBeforeUpdate();
            },
            getLocationForm(){

                return Object.assign({},
                    //  Set the default form details
                    {
                        name: '',
                        online: true,
                        about_us: '',
                        contact_us: '',
                        call_to_action: '',
                        offline_message: 'Sorry, we are currently offline',
                        

                        //  Delivery Details
                        allow_delivery: false,
                        delivery_note: '',
                        delivery_flat_fee: null,
                        delivery_destinations: null,
                        delivery_days: null,
                        delivery_times: null,

                        //  Pickup Details
                        pickup_note: '',
                        pickup_destinations: null,
                        pickup_days: null,
                        pickup_times: null,

                        //  Payment Details
                        allow_payments: false,
                        online_payment_methods: null,
                        offline_payment_methods: null,

                    //  Overide the default form details with the provided location details
                    }, this.location);

            },
            copyLocationBeforeUpdate(){
                
                //  Clone the location
                this.locationBeforeChanges = _.cloneDeep( this.locationForm );

            },
            locationHasBeenUpdated(){

                //  Check if the location has been modified
                return !_.isEqual(this.locationForm, this.locationBeforeChanges);

            },
            notifyUnsavedChangesStatus(){

                var status = this.locationHasBeenUpdated();

                //  Notify the parent component of the change status
                this.$emit('unsavedChanges', status);

                //  Update the local state of the change status
                this.locationHasChanged = status;

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
            fetchPaymentMethods() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingPaymentMethods = true;
                
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                let url = api_home['_links']['bos:payment_methods'].href;

                //  Use the api call() function, refer to api.js
                api.call('get', url)
                    .then(({data}) => {
                        
                        //  Console log the data returned
                        console.log(data);

                        //  Stop loader
                        self.isLoadingPaymentMethods = false;

                        //  Get the payment methods
                        self.paymentMethods = ((data || [])['_embedded'] || [])['payment_methods'];

                    })         
                    .catch(response => { 

                        //  Log the responce
                        console.error(response);

                        //  Stop loader
                        this.isLoadingPaymentMethods = false;

                    });
                
            },
            fetchLocation() {

                //  If we have the location url
                if( this.locationUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingLocation = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.locationUrl)
                        .then(({data}) => {
                            
                            //  Console log the data returned
                            console.log(data);

                            //  Get the location
                            self.location = data || null;

                            self.setFormAndCaptureBeforeChanges();

                            //  Capture the delivery destinations
                            ((self.location || {}).delivery_destinations || []).map((destination, index) => {
                                self.addDeliveryDestination(destination.name, destination.cost, self.deliveryDestinations);
                            });

                            //  Capture the delivery times
                            ((self.location || {}).delivery_times || []).map((time, index) => {
                                self.addDeliveryTime(time, self.deliveryTimes);
                            });

                            //  Capture the pickup destinations
                            ((self.location || {}).pickup_destinations || []).map((destination, index) => {
                                self.addPickupDestination(destination, self.pickupDestinations);
                            });

                            //  Capture the pickup times
                            ((self.location || {}).pickup_times || []).map((time, index) => {
                                self.addPickupTime(time, self.pickupTimes);
                            });

                            //  Stop loader
                            self.isLoadingLocation = false;

                        })         
                        .catch(response => { 

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isLoadingLocation = false;

                        });
                }
            },
            saveLocation() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                //  Notify parent that this component is saving data
                self.$emit('isSaving', self.isSavingChanges);

                /** Make an Api call to create the location. We include the
                 *  location details required for a new location creation.
                 */
                let locationData = this.locationForm;

                return api.call('put', this.location['_links']['self'].href, locationData)
                    .then(({data}) => {
                
                        console.log(data);

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
                
                        console.log(response);

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Notify parent that this component is not saving data
                        self.$emit('isSaving', self.isSavingChanges);

                        //  Get the error response data
                        let data = (response || {}).data;
                            
                        //  Get the response errors
                        var errors = (data || {}).errors;

                        //  Set the general error message
                        self.serverErrorMessage = (data || {}).message;

                        /** 422: Validation failed. Incorrect credentials
                         */
                        if((response || {}).status === 422){

                            //  If we have errors
                            if(_.size(errors)){
                                
                                //  Set the server errors
                                self.serverErrors = errors;

                                //  Foreach error
                                for (var i = 0; i < _.size(errors); i++) {

                                    //  Get the error key e.g 'name', 'dedicated_short_code'
                                    var prop = Object.keys(errors)[i];

                                    //  Get the error value e.g 'The location name is required'
                                    var value = Object.values(errors)[i][0];

                                    //  Dynamically update the serverErrors for View UI to display the error on the appropriate form item
                                    self.serverErrors[prop] = value;

                                }

                            }

                        }

                });
            },
            resetErrors(){
                this.serverErrorMessage = '';
                this.serverErrors = [];
            }
        },
        created(){
            this.fetchLocation();
            this.fetchPaymentMethods();
        }
    }
</script>