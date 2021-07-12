<template>

    <Row :gutter="12">

        <Col :span="24">

            <!-- If we are loading, Show Loader -->
            <Loader v-if="(isEditing && isLoadingPopularStore)" class="mb-2">Searching popular store</Loader>

            <!-- Popular Store Name, Statuses & Watch Video Button -->
            <Row v-else-if="(isEditing && !isLoadingPopularStore && localPopularStore) || !isEditing" :gutter="12">

                <Col :span="24">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isCreating" class="mb-2">Creating popular store</Loader>
                    <Loader v-else-if="isSavingChanges" class="mb-2">Saving popular store</Loader>
                    <Loader v-else-if="!popularStoreForm" class="mb-2">Preparing popular store</Loader>

                    <Form v-if="popularStoreForm" ref="popularStoreForm" :model="popularStoreForm" :rules="popularStoreFormRules">

                        <!-- Select Store Input -->
                        <storeSelectInput :popularStoreForm="popularStoreForm" :stores="stores" :popularStores="popularStores"
                                          :isLoadingStores="isLoadingStores" :isLoading="isLoading" :parentFetchStores="fetchStores"
                                          :serverErrors="serverErrors">
                        </storeSelectInput>

                        <!-- Position Input -->
                        <positionInput :popularStoreForm="popularStoreForm" :isEditing="isEditing" :isLoading="isLoading" :serverErrors="serverErrors"></positionInput>

                        <!-- Reset Dates Checkbox -->
                        <resetDatesCheckbox v-if="isEditing" :popularStoreForm="popularStoreForm" :isLoading="isLoading" :serverErrors="serverErrors"></resetDatesCheckbox>

                        <!-- Start At Input -->
                        <startAtInput :popularStoreForm="popularStoreForm" :isEditing="isEditing" :isLoading="isLoading" :serverErrors="serverErrors"></startAtInput>

                        <!-- Duration Input -->
                        <durationInput :popularStoreForm="popularStoreForm" :isEditing="isEditing" :isLoading="isLoading" :serverErrors="serverErrors"></durationInput>

                        <!-- If we are editting -->
                        <template v-if="isEditing">

                            <!-- Save Changes Button -->
                            <basicButton :disabled="(!popularStoreHasChanged || isSavingChanges)" :loading="isSavingChanges"
                                            :ripple="(popularStoreHasChanged && !isSavingChanges)" type="success" size="large"
                                            :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                            </basicButton>

                        </template>

                        <!-- If we are creating -->
                        <template v-if="!isEditing">

                            <!-- Create Button -->
                            <basicButton :disabled="(!popularStoreHasChanged || isCreating)" :loading="isCreating"
                                            :ripple="(popularStoreHasChanged && !isCreating)" type="success" size="large"
                                            :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                <span>{{ isCreating ? 'Creating...' : 'Create Popular Store' }}</span>
                            </basicButton>

                        </template>


                    </Form>

                </Col>

            </Row>

            <!-- If we are not loading and don't have the popular store -->
            <Alert v-else-if="(isEditing && !isLoadingPopularStore && !localPopularStore)" type="warning" class="mx-5" show-icon>
                Popular Store Not Found
                <template slot="desc">
                We could not get the popular store, try refreshing your browser. It's also possible that this popular store has been deleted.
                </template>
            </Alert>

        </Col>

    </Row>

</template>

<script>

    import moment from 'moment';
    import startAtInput from './components/startAtInput.vue';
    import positionInput from './components/positionInput.vue';
    import durationInput from './components/durationInput.vue';
    import storeSelectInput from './components/storeSelectInput.vue';
    import miscMixin from './../../../components/_mixins/misc/main.vue';
    import resetDatesCheckbox from './components/resetDatesCheckbox.vue';
    import Loader from './../../../components/_common/loaders/default.vue';
    import basicButton from './../../../components/_common/buttons/basicButton.vue';

    export default {
        mixins: [miscMixin],
        components: {
            startAtInput, positionInput, durationInput, storeSelectInput, resetDatesCheckbox, Loader, basicButton
        },
        props: {
            popularStore: {
                type: Object,
                default: null
            },
            popularStores: {
                type: Array,
                default: function(){
                    return [];
                }
            }
        },
        data(){
            return {
                stores: [],
                isCreating: false,
                popularStoreForm: null,
                localPopularStore: null,
                popularStoreFormRules: {

                },
                isSavingChanges: false,
                isLoadingStores: false,
                isLoadingPopularStore: false,
                popularStoreBeforeChanges: null,
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  Prepare the popular store
                this.preparePopularStore();

            }
        },
        computed: {
            isLoading(){
                return (
                    this.isLoadingPopularStore || this.isLoadingStores || this.isCreating || this.isSavingChanges
                );
            },
            popularStoreUrl(){
                return this.popularStore['_links']['self']['href'];
            },
            createPopularStoreUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:popular_stores'].href
            },
            storesUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:stores'].href
            },
            popularStoreHasChanged(){

                //  Check if the popular store has been modified
                var status = !_.isEqual(this.popularStoreForm, this.popularStoreBeforeChanges);

                return status;

            },
            isEditing(){
                return this.popularStore ? true : false;
            }
        },
        methods: {

            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async preparePopularStore(){

                if( this.isEditing ){

                    //  Reset tthe popular store form
                    this.popularStoreForm = null;

                    //  Fetch the popular store
                    await this.fetchPopularStore();

                    //  Notify parent of fetched popular store
                    this.$emit('fetchedPopularStore', this.localPopularStore);

                }

                //  Fetch the stores
                await this.fetchStores();

                //  Set the form details
                this.popularStoreForm = this.getPopularStoreForm();

                //  Save the form before any changes occur
                this.copyPopularStoreBeforeUpdate();

            },
            async fetchPopularStore() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingPopularStore = true;

                if( this.popularStoreUrl ){

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.popularStoreUrl)
                        .then(({data}) => {

                            //  Get the popular store
                            self.localPopularStore = data;

                            //  Stop loader
                            self.isLoadingPopularStore = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingPopularStore = false;

                        });
                }
            },
            async fetchStores() {

                if( this.storesUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingStores = true;

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.storesUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoadingStores = false;

                            //  Set the stores
                            self.stores = (((data || [])['_embedded'] || [])['stores'] || []);

                        })
                        .catch(response => {

                            //  Stop loader
                            this.isLoadingStores = false;

                        });

                }

            },
            getPopularStoreForm(){

                //  Clone the popular store Object (if any) as a new Object
                var form = _.cloneDeep(Object.assign({},
                    //  Set the default form details
                    {
                        //  Popular Store Management
                        store_id: null,
                        arrangement: 1,
                        duration: 1,
                        reset_dates: false,
                        start_at: moment(new Date()).format('YYYY-MM-DD HH:mm:00')

                    //  Overide the default form details with the provided popular store Object
                    }, this.localPopularStore));

                return form;

            },
            copyPopularStoreBeforeUpdate(){

                //  Clone the popular store before any changes occur
                this.popularStoreBeforeChanges = _.cloneDeep( this.popularStoreForm );

            },
            async handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the form
                this.$refs['popularStoreForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  If we are editing
                        if( this.isEditing ){

                            //  Attempt to save popular store
                            this.savePopularStore();

                        //  If we are creating
                        }else{

                            //  Attempt to create popular store
                            this.createPopularStore();

                        }

                    //  If the validation failed
                    } else {

                        //  If we are editing
                        if( this.isEditing ){

                            this.$Message.warning({
                                content: 'Sorry, you cannot update popular store yet',
                                duration: 6
                            });

                        //  If we are creating
                        }else{

                            this.$Message.warning({
                                content: 'Sorry, you cannot create popular store yet',
                                duration: 6
                            });

                        }

                    }
                });

            },
            savePopularStore() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                /** Make an Api call to create the popular store. We include the
                 *  popular store details required for a new popular store creation.
                 */
                let data = {
                    postData: this.popularStoreForm,
                };

                api.call('put', this.popularStoreUrl, data, this)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Instant cart updated success message
                        self.$Message.success({
                            content: 'Your popular store has been updated!',
                            duration: 6
                        });

                        self.copyPopularStoreBeforeUpdate();

                        //  Notify parent on changes
                        self.$emit('savedPopularStore', data);

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot update popular store',
                            duration: 6
                        });

                        //  Stop loader
                        self.isSavingChanges = false;

                });
            },
            createPopularStore() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                /** Make an Api call to create the popular store. We include the
                 *  popular store details required for a new popular store creation.
                 */
                let data = {
                        postData: this.popularStoreForm
                    };

                api.call('post', this.createPopularStoreUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent of the popular store created
                        self.$emit('createdPopularStore', data);

                        //  Instant cart created success message
                        self.$Message.success({
                            content: 'Your popular store has been created!',
                            duration: 6
                        });

                        //  resetForm() declared in miscMixin
                        self.resetForm('popularStoreForm');

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot create popular store',
                            duration: 6
                        });

                        //  Stop loader
                        self.isCreating = false;

                });
            },
        },
        created(){

            //  Prepare the popular store
            this.preparePopularStore();

        }
    };

</script>
