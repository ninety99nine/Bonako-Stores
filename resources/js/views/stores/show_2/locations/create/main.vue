<template>

    <Row>
        <Col span="12" :offset="6">

            <Button type="default" class="mt-5 mb-2" @click="navigateToLocations()">
                <Icon type="md-arrow-back" class="mr-1" :size="20" />
                <span>Locations</span>
            </Button>

            <Card class="pt-2">

                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold">Create Location</Divider>

                <div v-if="cloneLocation && !isSearchingLocation">

                    <!-- Clone Location Alert -->
                    <Alert type="info" show-icon>Cloning "{{ cloneLocation.name }}"</Alert>

                    <div>

                        <span class="font-weight-bold d-block mb-2">What would you like to clone?</span>

                        <!-- Clone Products Checkbox -->
                        <Checkbox v-model="locationForm.clone_products" class="ml-2">Products</Checkbox>

                    </div>

                    <Divider class="mt-2 mb-3"></Divider>

                </div>

                <template v-else>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isSearchingLocation" class="mt-2 mb-2">Searching for location to clone...</Loader>

                </template>

                <template v-if="!isSearchingLocation">

                    <!-- Error Message Alert -->
                    <Alert v-if="serverErrorMessage  && !isCreating" type="warning">{{ serverErrorMessage }}</Alert>

                    <Form ref="locationForm" :model="locationForm" :rules="locationFormRules">

                        <!-- Enter Name -->
                        <FormItem prop="name" :error="serverNameError">
                            <Input type="text" v-model="locationForm.name" placeholder="Name" :disabled="isCreating"
                                    maxlength="50" show-word-limit @keyup.enter.native="handleSubmit()">
                            </Input>
                        </FormItem>

                        <!-- Set Online Status -->
                        <FormItem prop="online" :error="serverOnlineError">
                            <div>
                                <span :style="{ width: '200px' }" class="font-weight-bold">{{ statusText }}: </span>
                                <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300"
                                        content="Turn on to allow subscribers to access this location location">
                                    <i-Switch v-model="locationForm.online" />
                                </Poptip>
                            </div>
                        </FormItem>

                        <!-- Set Offline Status Message -->
                        <FormItem v-if="!locationForm.online" prop="offline_message" :error="serverOfflineMessageError">
                            <div class="d-flex">
                                <span :style="{ width: '200px' }" class="font-weight-bold">Offline Message: </span>
                                <Input type="textarea" v-model="locationForm.offline_message" placeholder="Enter offline message" :disabled="isCreating"
                                        maxlength="160" show-word-limit @keyup.enter.native="handleSubmit()">
                                </Input>
                            </div>
                        </FormItem>

                        <!-- Call To Action -->
                        <FormItem prop="call_to_action" :error="serverCallToActionError">
                            <Input type="text" v-model="locationForm.call_to_action" placeholder="Call to action e.g Buy Grocery" :disabled="isCreating"
                                    maxlength="50" show-word-limit @keyup.enter.native="handleSubmit()">
                            </Input>
                        </FormItem>

                        <!-- Enter About Us -->
                        <FormItem prop="about_us" :error="serverAboutUsError">
                            <Input type="textarea" v-model="locationForm.about_us" placeholder="Describe this store location and its offerings" :disabled="isCreating"
                                    maxlength="140" show-word-limit @keyup.enter.native="handleSubmit()">
                            </Input>
                        </FormItem>

                        <!-- Enter Contact Us -->
                        <FormItem prop="contact_us" :error="serverContactUsError">
                            <Input type="textarea" v-model="locationForm.contact_us" placeholder="Enter contact information for this store location e.g phone number, email e.t.c" :disabled="isCreating"
                                    maxlength="140" show-word-limit @keyup.enter.native="handleSubmit()">
                            </Input>
                        </FormItem>

                        <!-- Allow Delivery -->
                        <FormItem prop="allow_delivery" :error="serverAllowDeliveryError">
                            <div>
                                <span :style="{ width: '200px' }" class="font-weight-bold">Allow Delivery: </span>
                                <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300"
                                        content="Turn on to allow delivery for orders placed to this location">
                                    <i-Switch v-model="locationForm.allow_delivery" />
                                </Poptip>
                            </div>
                        </FormItem>

                        <!-- Set Delivery Policy Message -->
                        <FormItem v-if="locationForm.allow_delivery" prop="delivery_note" :error="serverDeliveryPolicyError">
                            <div class="d-flex">
                                <span :style="{ width: '200px' }" class="font-weight-bold">Delivery Policy: </span>
                                <Input type="textarea" v-model="locationForm.delivery_note" placeholder="Enter delivery policy" :disabled="isCreating"
                                        maxlength="160" show-word-limit @keyup.enter.native="handleSubmit()">
                                </Input>
                            </div>
                        </FormItem>

                        <!-- Allow Payments -->
                        <FormItem prop="allow_payments" :error="serverAllowPaymentsError">
                            <div>
                                <span :style="{ width: '200px' }" class="font-weight-bold">Allow Payments: </span>
                                <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300"
                                        content="Turn on to allow payments for orders placed to this location">
                                    <i-Switch v-model="locationForm.allow_payments" />
                                </Poptip>
                            </div>
                        </FormItem>

                        <!-- Create Button -->
                        <FormItem v-if="!isCreating">
                            <Button type="primary" class="float-right" :disabled="isSearchingLocation || isCreating" @click="handleSubmit()">Create Location</Button>
                        </FormItem>

                        <!-- If we are loading, Show Loader -->
                        <Loader v-show="isCreating" class="mt-2">Creating location...</Loader>

                    </Form>

                    {{ this.cloneLocation }}

                </template>

            </Card>
        </Col>
    </Row>

</template>
<script>

    import Loader from './../../../../../components/_common/loaders/default.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';

    export default {
        mixins: [miscMixin],
        components: { Loader },
        props: {
            store: {
                type: Object,
                default: null
            }
        },
        data () {

            return {
                cloneLocation: null,
                isSearchingLocation: false,
                isCreating: false,
                locationForm: null,
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
                        { required: true, message: 'Please enter your delivery policy e.g We only deliver on Mondays and Fridays at 9am and 4pm', trigger: 'blur' },
                        { min: 3, message: 'Delivery policy is too short', trigger: 'change' },
                        { max: 140, message: 'Delivery policy is too long', trigger: 'change' }
                    ]
                },
                user: auth.getUser()
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
            serverAllowDeliveryError(){
                return (this.serverErrors || {}).allow_delivery;
            },
            serverDeliveryPolicyError(){
                return (this.serverErrors || {}).delivery_note;
            },
            serverAllowPaymentsError(){
                return (this.serverErrors || {}).allow_payments;
            },
            cloneLocationUrl(){
                if( this.$route.query.location_url ){
                    return decodeURIComponent(this.$route.query.location_url);
                }
            },
            statusText(){
                return this.locationForm.online ? 'Online' : 'Offline';
            }
        },
        methods: {
            navigateToLocations(){

                //  Redirect the user to the locations page
                this.$router.push({ name: 'show-locations' });

            },
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the form
                this.$refs['locationForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  Attempt to create the location
                        this.attemptLocationCreation();

                    //  If the validation failed
                    } else {
                        this.$Message.warning({
                            content: 'Sorry, you cannot create your location yet',
                            duration: 6
                        });
                    }
                })
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
                        allow_delivery: false,
                        delivery_note: '',
                        allow_payments: false,

                        store_id: this.store.id,
                        clone_location_id: (this.cloneLocation || {}).id

                    //  Overide the default form details with the provided location details to clone
                    }, this.cloneLocation);

            },
            fetchLocationToClone() {

                //  If we have the location url
                if( this.cloneLocationUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isSearchingLocation = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.cloneLocationUrl)
                        .then(({data}) => {

                            //  Get the location
                            self.cloneLocation = data || null;

                            self.locationForm = self.getLocationForm();

                            //  Stop loader
                            self.isSearchingLocation = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isSearchingLocation = false;

                        });
                }
            },
            attemptLocationCreation(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                let url = api_home['_links']['bos:locations'].href

                /**  Make an Api call to create the location. We include the
                 *   location details required for a new location creation.
                 */
                let data = {
                        postData: this.locationForm
                    };

                return api.call('post', url, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  resetForm() declared in miscMixin
                        self.resetForm('locationForm');

                        //  Location created success message
                        self.$Message.success({
                            content: 'Your location has been created!',
                            duration: 6
                        });

                        //  Redirect the user to the locations page
                        this.$router.push({ name: 'show-locations' });

                    }).catch((response) => {

                        //  Stop loader
                        self.isCreating = false;

                });
            }
        },
        created() {

            //  If we have the location url
            if( this.cloneLocationUrl ){

                this.fetchLocationToClone();

            }else{

                this.locationForm = this.getLocationForm();

            }

        }
    }
</script>
