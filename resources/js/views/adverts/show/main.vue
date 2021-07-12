<template>

    <Row :gutter="12">

        <Col :span="24">

            <!-- If we are loading, Show Loader -->
            <Loader v-if="(isEditing && isLoadingAdvert)" class="mb-2">Searching advert</Loader>

            <!-- Advert Name, Statuses & Watch Video Button -->
            <Row v-else-if="(isEditing && !isLoadingAdvert && localAdvert) || !isEditing" :gutter="12">

                <Col :span="24">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isCreating" class="mb-2">Creating advert</Loader>
                    <Loader v-else-if="isSavingChanges" class="mb-2">Saving advert</Loader>
                    <Loader v-else-if="!advertForm" class="mb-2">Preparing advert</Loader>

                    <Form v-if="advertForm" ref="advertForm" :model="advertForm" :rules="advertFormRules">

                        <!-- Name Input -->
                        <nameInput :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></nameInput>

                        <!-- Date Heading -->
                        <Divider orientation="left" class="font-weight-bold mt-4">Advert Details</Divider>

                        <!-- Title Input -->
                        <titleInput :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></titleInput>

                        <!-- Message Input -->
                        <messageInput :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></messageInput>

                        <!-- Position Input -->
                        <positionInput :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></positionInput>

                        <!-- Date Heading -->
                        <Divider orientation="left" class="font-weight-bold mt-4">Advert Action</Divider>

                        <!-- Call To Action Input -->
                        <callToActionInput :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></callToActionInput>

                        <!-- Resource Type Select Input -->
                        <resourceTypeSelectInput :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></resourceTypeSelectInput>

                        <!-- Select Store Input -->
                        <storeSelectInput v-if="advertForm.resource_type == 'store'" :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></storeSelectInput>

                        <!-- Select Location Input -->
                        <locationSelectInput v-if="advertForm.resource_type == 'location'" :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></locationSelectInput>

                        <!-- Select Instant Cart Input -->
                        <instantCartSelectInput v-if="advertForm.resource_type == 'instant_cart'" :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></instantCartSelectInput>

                        <!-- Date Heading -->
                        <Divider orientation="left" class="font-weight-bold mt-4">Advert Date</Divider>

                        <!-- Reset Dates Checkbox -->
                        <resetDatesCheckbox v-if="isEditing" :advertForm="advertForm" :isLoading="isLoading" :serverErrors="serverErrors"></resetDatesCheckbox>

                        <!-- Start At Input -->
                        <startAtInput :advertForm="advertForm" :isEditing="isEditing" :isLoading="isLoading" :serverErrors="serverErrors"></startAtInput>

                        <!-- Duration Input -->
                        <durationInput :advertForm="advertForm" :isEditing="isEditing" :isLoading="isLoading" :serverErrors="serverErrors"></durationInput>

                        <!-- If we are editting -->
                        <template v-if="isEditing">

                            <!-- Save Changes Button -->
                            <basicButton :disabled="(!advertHasChanged || isSavingChanges)" :loading="isSavingChanges"
                                            :ripple="(advertHasChanged && !isSavingChanges)" type="success" size="large"
                                            :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                            </basicButton>

                        </template>

                        <!-- If we are creating -->
                        <template v-if="!isEditing">

                            <!-- Create Button -->
                            <basicButton :disabled="(!advertHasChanged || isCreating)" :loading="isCreating"
                                            :ripple="(advertHasChanged && !isCreating)" type="success" size="large"
                                            :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                <span>{{ isCreating ? 'Creating...' : 'Create Advert' }}</span>
                            </basicButton>

                        </template>


                    </Form>

                </Col>

            </Row>

            <!-- If we are not loading and don't have the advert -->
            <Alert v-else-if="(isEditing && !isLoadingAdvert && !localAdvert)" type="warning" class="mx-5" show-icon>
                Advert Not Found
                <template slot="desc">
                We could not get the advert, try refreshing your browser. It's also possible that this advert has been deleted.
                </template>
            </Alert>

        </Col>

    </Row>

</template>

<script>

    import moment from 'moment';
    import nameInput from './components/nameInput.vue';
    import titleInput from './components/titleInput.vue';
    import startAtInput from './components/startAtInput.vue';
    import messageInput from './components/messageInput.vue';
    import durationInput from './components/durationInput.vue';
    import positionInput from './components/positionInput.vue';
    import storeSelectInput from './components/storeSelectInput.vue';
    import callToActionInput from './components/callToActionInput.vue';
    import miscMixin from './../../../components/_mixins/misc/main.vue';
    import resetDatesCheckbox from './components/resetDatesCheckbox.vue';
    import Loader from './../../../components/_common/loaders/default.vue';
    import locationSelectInput from './components/locationSelectInput.vue';
    import instantCartSelectInput from './components/instantCartSelectInput.vue';
    import resourceTypeSelectInput from './components/resourceTypeSelectInput.vue';
    import basicButton from './../../../components/_common/buttons/basicButton.vue';



    export default {
        mixins: [miscMixin],
        components: {
            nameInput, titleInput, startAtInput, messageInput, durationInput, positionInput, storeSelectInput,
            callToActionInput, resetDatesCheckbox, Loader, locationSelectInput, instantCartSelectInput,
            resourceTypeSelectInput, basicButton
        },
        props: {
            advert: {
                type: Object,
                default: null
            },
            adverts: {
                type: Array,
                default: function(){
                    return [];
                }
            }
        },
        data(){
            return {
                isCreating: false,
                advertForm: null,
                localAdvert: null,
                advertFormRules: {

                },
                isSavingChanges: false,
                isLoadingAdvert: false,
                advertBeforeChanges: null,
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  Prepare the advert
                this.prepareAdvert();

            }
        },
        computed: {
            isLoading(){
                return (
                    this.isLoadingAdvert || this.isCreating || this.isSavingChanges
                );
            },
            advertUrl(){
                return this.advert['_links']['self']['href'];
            },
            createAdvertUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:adverts'].href
            },
            advertHasChanged(){

                //  Check if the advert has been modified
                var status = !_.isEqual(this.advertForm, this.advertBeforeChanges);

                return status;

            },
            isEditing(){
                return this.advert ? true : false;
            }
        },
        methods: {

            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async prepareAdvert(){

                if( this.isEditing ){

                    //  Reset tthe advert form
                    this.advertForm = null;

                    //  Fetch the advert
                    await this.fetchAdvert();

                    //  Notify parent of fetched advert
                    this.$emit('fetchedAdvert', this.localAdvert);

                }

                //  Set the form details
                this.advertForm = this.getAdvertForm();

                //  Save the form before any changes occur
                this.copyAdvertBeforeUpdate();

            },
            async fetchAdvert() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingAdvert = true;

                if( this.advertUrl ){

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.advertUrl)
                        .then(({data}) => {

                            //  Get the advert
                            self.localAdvert = data;

                            //  Stop loader
                            self.isLoadingAdvert = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingAdvert = false;

                        });
                }
            },
            getAdvertForm(){

                //  Clone the advert Object (if any) as a new Object
                var form = _.cloneDeep(Object.assign({},
                    //  Set the default form details
                    {
                        //  Advert Management
                        name: '',
                        title: '',
                        message: '',
                        call_to_action: '',
                        arrangement: 1,

                        duration: 1,
                        reset_dates: false,
                        start_at: moment(new Date()).format('YYYY-MM-DD HH:mm:00'),

                        allow_limited_views: false,
                        limited_views: 10,

                        resource_id: null,
                        resource_type: 'store'

                    //  Overide the default form details with the provided advert Object
                    }, this.localAdvert));

                    if( this.localAdvert ){

                        form.resource_id = this.localAdvert.owner_id;
                        form.resource_type = this.localAdvert.owner_type;

                    }

                return form;

            },
            copyAdvertBeforeUpdate(){

                //  Clone the advert before any changes occur
                this.advertBeforeChanges = _.cloneDeep( this.advertForm );

            },
            async handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the form
                this.$refs['advertForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  If we are editing
                        if( this.isEditing ){

                            //  Attempt to save advert
                            this.saveAdvert();

                        //  If we are creating
                        }else{

                            //  Attempt to create advert
                            this.createAdvert();

                        }

                    //  If the validation failed
                    } else {

                        //  If we are editing
                        if( this.isEditing ){

                            this.$Message.warning({
                                content: 'Sorry, you cannot update advert yet',
                                duration: 6
                            });

                        //  If we are creating
                        }else{

                            this.$Message.warning({
                                content: 'Sorry, you cannot create advert yet',
                                duration: 6
                            });

                        }

                    }
                });

            },
            saveAdvert() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                /** Make an Api call to create the advert. We include the
                 *  advert details required for a new advert creation.
                 */
                let data = {
                    postData: this.advertForm,
                };

                api.call('put', this.advertUrl, data, this)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Instant cart updated success message
                        self.$Message.success({
                            content: 'Your advert has been updated!',
                            duration: 6
                        });

                        self.copyAdvertBeforeUpdate();

                        //  Notify parent on changes
                        self.$emit('savedAdvert', data);

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot update advert',
                            duration: 6
                        });

                        //  Stop loader
                        self.isSavingChanges = false;

                });
            },
            createAdvert() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                /** Make an Api call to create the advert. We include the
                 *  advert details required for a new advert creation.
                 */
                let data = {
                        postData: this.advertForm
                    };

                api.call('post', this.createAdvertUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent of the advert created
                        self.$emit('createdAdvert', data);

                        //  Instant cart created success message
                        self.$Message.success({
                            content: 'Your advert has been created!',
                            duration: 6
                        });

                        //  resetForm() declared in miscMixin
                        self.resetForm('advertForm');

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot create advert',
                            duration: 6
                        });

                        //  Stop loader
                        self.isCreating = false;

                });
            },
        },
        created(){

            //  Prepare the advert
            this.prepareAdvert();

        }
    };

</script>
