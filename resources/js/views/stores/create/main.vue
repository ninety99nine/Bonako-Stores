<template>

    <Row>
        <Col span="8" :offset="8">

            <Button type="default" class="mt-5 mb-2" @click="navigateToStores()">
                <Icon type="md-arrow-back" class="mr-1" :size="20" />
                <span>Stores</span>
            </Button>

            <Card class="pt-2">

                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold">Create Store</Divider>

                <div v-if="cloneStore && !isSearching">

                    <!-- Clone Store Alert -->
                    <Alert type="info" show-icon>Cloning "{{ cloneStore.name }}"</Alert>

                    <div>

                        <span class="font-weight-bold d-block mb-2">What would you like to clone?</span>

                        <!-- Clone Locations Checkbox -->
                        <Checkbox v-model="storeForm.clone_locations" class="ml-2">Locations</Checkbox>

                        <!-- Clone Products Checkbox -->
                        <Checkbox v-model="storeForm.clone_products" class="ml-2">Products</Checkbox>

                        <!-- Clone Discounts Checkbox -->
                        <Checkbox v-model="storeForm.clone_discounts" class="ml-2">Discounts</Checkbox>

                        <!-- Clone Coupons Checkbox -->
                        <Checkbox v-model="storeForm.clone_coupons" class="ml-2">Coupons</Checkbox>

                    </div>

                </div>

                <template v-else>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isSearching && !isCreating" class="mt-2 mb-2">Searching for store to clone...</Loader>

                </template>

                <!-- Error Message Alert -->
                <Alert v-if="serverErrorMessage  && !isCreating" type="warning">{{ serverErrorMessage }}</Alert>

                <Form ref="storeForm" :model="storeForm" :rules="storeFormRules">

                    <!-- Enter Name -->
                    <FormItem prop="name" :error="serverNameError">
                        <Input type="text" v-model="storeForm.name" placeholder="Name" :disabled="isCreating"
                                maxlength="50" show-word-limit @keyup.enter.native="handleSubmit()">
                        </Input>
                    </FormItem>

                    <!-- Call To Action -->
                    <FormItem :error="serverCallToActionError">
                        <Input type="text" v-model="storeForm.location.call_to_action" placeholder="Call to action e.g Buy Grocery" :disabled="isCreating"
                                maxlength="50" show-word-limit @keyup.enter.native="handleSubmit()">
                        </Input>
                    </FormItem>

                    <!-- Set Online Status -->
                    <FormItem prop="online" :error="serverOnlineError">
                        <div>
                            <span :style="{ width: '200px' }" class="font-weight-bold">{{ statusText }}: </span>
                            <Poptip trigger="hover" title="Turn On/Off" word-wrap width="300"
                                    content="Turn on to allow subscribers to access this store">
                                <i-Switch v-model="storeForm.online" />
                            </Poptip>
                        </div>
                    </FormItem>

                    <!-- Set Offline Status Message -->
                    <FormItem v-if="!storeForm.online" prop="offline_message" :error="serverOfflineMessageError">
                        <div class="d-flex">
                            <span :style="{ width: '200px' }" class="font-weight-bold">Offline Message: </span>
                            <Input type="textarea" v-model="storeForm.offline_message" placeholder="Enter offline message" :disabled="isSavingChanges"
                                    maxlength="160" show-word-limit @keyup.enter.native="handleSubmit()">
                            </Input>
                        </div>
                    </FormItem>

                    <!-- Clone Coupons Checkbox -->
                    <Checkbox v-model="storeForm.allow_sending_merchant_sms" class="mr-4">Send alerts via SMS</Checkbox>

                    <span class="d-inline-block">
                        <span class="font-weight-bold">Highlighter</span>:
                        <ColorPicker v-model="storeForm.hex_color" recommend></ColorPicker>
                    </span>

                    <!-- Create Button -->
                    <FormItem v-if="!isCreating">
                        <Button type="primary" class="float-right" :disabled="isSearching || isCreating" @click="handleSubmit()">Create Store</Button>
                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isCreating" class="mt-2">Creating store...</Loader>

                </Form>

            </Card>
        </Col>
    </Row>

</template>
<script>

    import miscMixin from './../../../components/_mixins/misc/main.vue';
    import Loader from './../../../components/_common/loaders/default.vue';

    export default {
        mixins: [miscMixin],
        components: { Loader },
        data () {

            return {
                cloneStore: null,
                isSearching: false,
                isCreating: false,
                isLoadingSubscriptionPlans: false,
                storeForm: {
                    name: '',
                    online: true,
                    allow_sending_merchant_sms: true,
                    offline_message: 'Sorry, we are currently offline',
                    hex_color: '2D8CF0',
                    location: {
                        online: true,
                        call_to_action: '',
                    },
                    clone_locations: true,
                    clone_products: true,
                    clone_discounts: true,
                    clone_coupons: true,
                },
                storeFormRules: {
                    name: [
                        { required: true, message: 'Please enter your store name', trigger: 'blur' },
                        { min: 3, message: 'Store name is too short', trigger: 'change' },
                        { max: 50, message: 'Store name is too long', trigger: 'change' }
                    ],
                    call_to_action: [
                        { required: true, message: 'Please enter your call to action e.g Buy Grocery', trigger: 'blur' },
                        { min: 3, message: 'Call to action is too short', trigger: 'change' },
                        { max: 140, message: 'Call to action is too long', trigger: 'change' }
                    ],
                    offline_message: [
                        { min: 3, message: 'Offline message is too short', trigger: 'change' },
                        { max: 160, message: 'Offline message is too long', trigger: 'change' }
                    ]
                },
                user: auth.getUser()
            }
        },
        computed: {
            serverNameError(){
                return (this.serverErrors || {}).name;
            },
            serverCallToActionError(){
                return (this.serverErrors || {}).call_to_action;
            },
            serverOnlineError(){
                return (this.serverErrors || {}).online;
            },
            cloneStoreUrl(){
                if( this.$route.query.store_url ){
                    return decodeURIComponent(this.$route.query.store_url);
                }
            },
            statusText(){
                return this.storeForm.online ? 'Online' : 'Offline';
            },
            createStoreUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:stores'].href
            }
        },
        methods: {
            navigateToStores(){

                //  Redirect the user to the stores page
                this.$router.push({ name: 'show-stores' });

            },
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the form
                this.$refs['storeForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  Attempt to create the store
                        this.attemptStoreCreation();

                    //  If the validation failed
                    } else {
                        this.$Message.warning({
                            content: 'Sorry, you cannot create your store yet',
                            duration: 6
                        });
                    }
                })
            },
            fetchStoreToClone() {

                //  If we have the store url
                if( this.cloneStoreUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isSearching = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.cloneStoreUrl)
                        .then(({data}) => {

                            //  Get the store
                            self.cloneStore = data || null;

                            //  Update the clone store id
                            self.$set(self.storeForm, 'clone_store_id', (data || {}).id);

                            //  Stop loader
                            self.isSearching = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isSearching = false;

                        });
                }
            },
            attemptStoreCreation(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                /**  Make an Api call to create the store. We include the
                 *   store details required for a new store creation.
                 */
                let data = {
                    postData: this.storeForm
                };

                return api.call('post', this.createStoreUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  resetForm() declared in miscMixin
                        self.resetForm('storeForm');

                        //  Store created success message
                        self.$Message.success({
                            content: 'Your store has been created!',
                            duration: 6
                        });

                        //  Redirect the user to the stores page
                        this.$router.push({ name: 'show-stores' });

                    }).catch((response) => {

                        //  Stop loader
                        self.isCreating = false;

                });
            }
        },
        created() {
            this.fetchStoreToClone();
        }
    }
</script>
