<template>

    <Row>

        <Col :span="12" :offset="6">

            <Card class="mt-3 pt-2">

                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold">Store Details</Divider>

                <!-- Server Error Message Alert -->
                <Alert v-if="serverErrorMessage && !isSavingChanges" type="warning">{{ serverErrorMessage }}</Alert>

                <Form ref="storeForm" :model="storeForm" :rules="storeFormRules">

                    <!-- Enter Name -->
                    <FormItem prop="name" :error="serverNameError">
                        <Input type="text" v-model="storeForm.name" placeholder="Name" :disabled="isSavingChanges"
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
                            <Input type="textarea" v-model="storeForm.offline_message" placeholder="offline_message" :disabled="isSavingChanges"
                                    maxlength="160" show-word-limit @keyup.enter.native="handleSubmit()">
                            </Input>
                        </div>
                    </FormItem>

                    <!-- Save Changes Button -->
                    <FormItem v-if="!isSavingChanges">

                        <basicButton :disabled="(!storeHasChanged || isSavingChanges)" :loading="isSavingChanges"
                                     :ripple="(storeHasChanged && !isSavingChanges)" type="success" size="large"
                                     class="float-right" @click.native="handleSubmit()">
                            <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                        </basicButton>

                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isSavingChanges" class="mt-2">Saving store...</Loader>

                </Form>
            </Card>
        </Col>
    </Row>

</template>
<script>

    import basicButton from './../../../../components/_common/buttons/basicButton.vue';
    import miscMixin from './../../../../components/_mixins/misc/main.vue';
    import Loader from './../../../../components/_common/loaders/default.vue';

    export default {
        mixins: [miscMixin],
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
        components: { basicButton, Loader },
        data () {

            return {
                storeForm: null,
                isSavingChanges: false,
                storeHasChanged: false,
                storeBeforeChanges: null,
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
                }
            }
        },
        watch: {
            //  Keep track of changes on the store
            store: {

                handler: function (val, oldVal) {

                    this.setup();

                },
                deep: true

            },
            //  Keep track of changes on the store form
            storeForm: {

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
            statusText(){
                return this.storeForm.online ? 'Online' : 'Offline'
            },
            storeUrl(){
                return decodeURIComponent(this.$route.params.store_url);
            }
        },
        methods: {
            setup(){
                this.storeForm = this.getStoreForm();
                this.copyStoreBeforeUpdate();
            },
            getStoreForm(){

                return Object.assign({},
                    //  Set the default form details
                    {
                        name: '',
                        online: false,
                        offline_message: ''

                    //  Overide the default form details with the provided store details
                    }, this.store);

            },
            copyStoreBeforeUpdate(){

                //  Clone the store
                this.storeBeforeChanges = _.cloneDeep( this.storeForm );

            },
            storeHasBeenUpdated(){

                //  Check if the store has been modified
                return !_.isEqual(this.storeForm, this.storeBeforeChanges);

            },
            notifyUnsavedChangesStatus(){

                var status = this.storeHasBeenUpdated();

                //  Notify the parent if we have changes to save
                this.$emit('unsavedChanges', status);

                this.storeHasChanged = status;

            },
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the store form
                this.$refs['storeForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  Attempt to create store
                        this.attemptStoreUpdate();

                    //  If the validation failed
                    } else {
                        this.$Message.warning({
                            content: 'Sorry, you cannot update yet',
                            duration: 6
                        });
                    }
                })
            },
            attemptStoreUpdate(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;
                self.$emit('isSaving', self.isSavingChanges);

                /**  Make an Api call to create the store. We include the
                 *   store details required for a new store creation.
                 */
                let data = {
                        postData: this.storeForm
                    };

                return api.call('put', this.store['_links']['self'].href, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;
                        self.$emit('isSaving', self.isSavingChanges);

                        self.$emit('updatedStore', data);

                        //  resetForm() declared in miscMixin
                        self.resetForm('storeForm');

                        //  Store updated success message
                        self.$Message.success({
                            content: 'Your store has been updated!',
                            duration: 6
                        });

                        self.copyStoreBeforeUpdate();

                        self.notifyUnsavedChangesStatus();

                    }).catch((response) => {

                        //  Stop loader
                        self.isSavingChanges = false;
                        self.$emit('isSaving', self.isSavingChanges);

                });
            }
        },
        created(){
            this.setup();
        }
    }
</script>
