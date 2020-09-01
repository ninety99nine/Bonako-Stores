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
                    <Loader v-if="isSearching && !isLoading" class="mt-2 mb-2">Searching for store to clone...</Loader>

                </template>

                <!-- Error Message Alert -->
                <Alert v-if="serverErrorMessage  && !isLoading" type="warning">{{ serverErrorMessage }}</Alert>
                <Alert v-if="serverGeneralError  && !isLoading" type="warning">{{ serverGeneralError }}</Alert>

                <Form ref="storeForm" :model="storeForm" :rules="storeFormRules">
                    
                    <!-- Enter Name -->
                    <FormItem prop="name" :error="serverNameError">
                        <Input type="text" v-model="storeForm.name" placeholder="Name" :disabled="isLoading" 
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

                    <!-- Create Button -->
                    <FormItem v-if="!isLoading">
                        <Button type="primary" class="float-right" :disabled="isSearching || isLoading" @click="handleSubmit()">Create Store</Button>
                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isLoading" class="mt-2">Creating store...</Loader>

                </Form>
                
            </Card>
        </Col>
    </Row>

</template>
<script>
    
    import Loader from './../../../components/_common/loaders/default.vue';

    export default {
        components: { Loader },
        data () {

            return {
                cloneStore: null,
                isSearching: false,
                isLoading: false,
                storeForm: {
                    name: '',
                    online: true,
                    offline_message: 'Sorry, we are currently offline',

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
                    offline_message: [
                        { min: 3, message: 'Offline message is too short', trigger: 'change' },
                        { max: 160, message: 'Offline message is too long', trigger: 'change' }
                    ]
                },
                serverErrors: [],
                serverErrorMessage: '',
                user: auth.getUser()
            }
        },
        computed: {
            serverGeneralError(){
                return (this.serverErrors || {}).general;
            },
            serverNameError(){
                return (this.serverErrors || {}).name;
            },
            serverOnlineError(){
                return (this.serverErrors || {}).online;
            },
            serverSharedShortCodeError(){
                return (this.serverErrors || {}).shared_short_code;
            },
            cloneStoreUrl(){
                if( this.$route.query.store_url ){
                    return decodeURIComponent(this.$route.query.store_url);
                }
            },
            statusText(){
                return this.storeForm.online ? 'Online' : 'Offline';
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
                            
                            //  Console log the data returned
                            console.log(data);

                            //  Get the store
                            self.cloneStore = data || null;

                            //  Update the clone store id
                            self.$set(self.storeForm, 'clone_store_id', (data || {}).id);

                            //  Stop loader
                            self.isSearching = false;

                        })         
                        .catch(response => { 

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isSearching = false;

                        });
                }
            },
            attemptStoreCreation(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                /**  Make an Api call to create the store. We include the
                 *   store details required for a new store creation.
                 */
                let storeData = this.storeForm;
                
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                let url = api_home['_links']['bos:stores'].href

                return api.call('post', url, storeData)
                    .then(({data}) => {

                        //  Stop loader
                        self.isLoading = false;

                        //  Reset the form
                        self.resetStoreForm();

                        //  Store created success message
                        self.$Message.success({
                            content: 'Your store has been created!',
                            duration: 6
                        });

                        //  Redirect the user to the stores page
                        this.$router.push({ name: 'show-stores' });
                        
                    }).catch((response) => {
                
                        console.log(response);

                        //  Stop loader
                        self.isLoading = false;

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
                                    //  Get the error key e.g 'email', 'password'
                                    var prop = Object.keys(errors)[i];
                                    //  Get the error value e.g 'These credentials do not match our records.'
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
            },
            resetStoreForm(){
                this.resetErrors();
                this.$refs['storeForm'].resetFields();
            }
        },
        created() {
            this.fetchStoreToClone();   
        }
    }
</script>