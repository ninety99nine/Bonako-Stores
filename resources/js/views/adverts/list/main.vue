<template>

    <Row :gutter="12">

        <Col :span="20" :offset="2">

            <!-- Heading, Add Advert Button & Watch Video Button -->
            <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                <Col :span="12">

                    <!-- Heading -->
                    <h1 :class="['font-weight-bold', 'text-muted']">Adverts</h1>

                </Col>

                <Col :span="12">

                    <div class="clearfix">

                        <!-- Add Advert Button -->
                        <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true"
                                    :ripple="!advertsExist && !isLoading" :class="['float-right', 'ml-2']"
                                    :disabled="(advertsExist && advertsHaveChanged) || isLoading"
                                    @click.native="handleAddAdvert()">
                            <span>Add Advert</span>
                        </basicButton>

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" @click.native="fetchAdverts()" :class="['float-right']">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

            </Row>

            <!-- Search Bar, Filters, Arrange Adverts Switch, Save Changes Button & Refresh Button -->
            <Row :gutter="12" class="mb-4">

                <Col :span="8">

                    <!-- Search Bar -->
                    <Input v-model="searchWord" type="text" size="default" :disabled="isLoading || arrangeAdverts" clearable placeholder="Search advert name, title or message" icon="ios-search-outline"></Input>

                </Col>

                <Col :span="8">

                    <!-- Filters -->
                    <Poptip trigger="hover" content="Add filters for adverts" word-wrap class="poptip-w-100">
                        <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                prefix="ios-funnel-outline" clearable placeholder="Add filters"
                                :disabled="isLoading || arrangeAdverts" @on-select="fetchAdverts()">

                            <!-- Filter Options-->
                            <Option v-for="(status, index) in statuses" :value="status.name" :key="index" :label="status.name">
                                <span :class="['font-weight-bold']">{{ status.name }}</span>
                                <span v-if="status.desc" style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">{{ status.desc }}</span>
                            </Option>

                        </Select>
                    </Poptip>

                </Col>

                <Col :span="8" :class="['clearfix']">

                    <!-- Arrange Adverts Switch -->
                    <div v-if="!advertsHaveChanged" :class="['float-left', 'mt-1', 'ml-3']">
                        <span :style="{ width: '200px' }" class="font-weight-bold">Arrange Adverts: </span>
                        <Poptip trigger="hover" word-wrap width="300" content="Turn on to drag and drop and change the arrangement of adverts">
                            <i-Switch v-model="arrangeAdverts" :disabled="!adverts.length || isLoading || advertsHaveChanged" />
                        </Poptip>
                    </div>

                    <template v-if="adverts.length && advertsHaveChanged">

                        <!-- Save Changes Button -->
                        <basicButton type="success" size="default"
                                    :class="['float-right', 'ml-2']" :ripple="advertsHaveChanged && !isLoading"
                                    :disabled="isLoading" :loading="isLoading"
                                    @click.native="updateAdvertArrangement()">
                            <span>Save Changes</span>
                        </basicButton>

                        <Button v-if="advertsHaveChanged" type="default"
                                size="default" :class="['float-right']"
                                @click.native="resetAdverts()"
                                :disabled="isLoading">
                            <span>Cancel</span>
                        </Button>

                    </template>

                    <!-- Refresh Button -->
                    <Button v-else type="default" size="default" :class="['float-right']"
                        :loading="isLoading" :disabled="isLoading"
                        @click.native="fetchAdverts()">
                        <Icon v-show="!isLoading" type="ios-refresh" class="mr-1" :size="20" />
                        <span>Refresh</span>
                    </Button>

                </Col>

            </Row>


            <!-- Product Table -->
            <Table v-show="!arrangeAdverts" :columns="dynamicColumns" :class="['w-100']"
                    :data="adverts" :loading="isLoading" no-data-text="No adverts found"
                    :style="{ overflow: 'visible' }">

                <!-- ID Poptip -->
                <idPoptip slot-scope="{ row, index }" slot="id" :advert="row"></idPoptip>

                <!-- Name Poptip -->
                <namePoptip slot-scope="{ row, index }" slot="name" :advert="row"></namePoptip>

                <!-- Visible Poptip -->
                <visibleStatusBadge slot-scope="{ row, index }" slot="visible" :advert="row"></visibleStatusBadge>

                <!-- Start At Poptip -->
                <startAtPoptip slot-scope="{ row, index }" slot="start date" :advert="row"></startAtPoptip>

                <!-- End At Poptip -->
                <endAtPoptip slot-scope="{ row, index }" slot="end date" :advert="row"></endAtPoptip>

                <!-- Position Poptip -->
                <positionPoptip slot-scope="{ row, index }" slot="position" :advert="row"></positionPoptip>

                <template slot-scope="{ row, index }" slot="action">

                    <div>
                        <Dropdown trigger="click" placement="bottom-end">
                            <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                            <DropdownMenu slot="list">
                                <DropdownItem name="Edit" @click.native="handleEditAdvert(row, index)">Edit</DropdownItem>
                                <DropdownItem name="Remove" class="text-danger" @click.native="handleDeleteAdvert(row, index)">Remove</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </div>

                </template>

            </Table>

            <!-- Draggable Product Cards (Arrange Adverts) -->
            <draggable v-show="arrangeAdverts" v-model="adverts" draggable=".draggable-advert">

                <Card v-for="(advert, index) in adverts" :key="index" :class="['draggable-advert', 'cursor-pointer', 'mb-1']">

                    <div :class="['clearfix']">

                        <!-- Product Name  -->
                        <span :class="['float-left', 'font-weight-bold']">{{ advert.name }}</span>

                        <!-- Move Product Button  -->
                        <Icon :class="['float-right', 'dragger-handle']" type="ios-move" size="20" />

                        <!-- Product Position  -->
                        <span :class="['float-right', 'border-bottom', 'mr-5']"># {{ (index + 1) }}</span>

                    </div>

                </Card>

            </draggable>

        </Col>

        <!--
            MODAL TO CREATE / EDIT Advert
        -->
        <template v-if="isOpenManageAdvertModal">

            <manageAdvertDrawer
                :index="index"
                :advert="advert"
                :adverts="adverts"
                @savedAdvert="handleSavedAdvert($event)"
                @createdAdvert="handleCreatedAdvert($event)"
                @visibility="isOpenManageAdvertModal = $event">
            </manageAdvertDrawer>

        </template>

        <!--
            MODAL TO DELETE Advert
        -->
        <template v-if="isOpenDeleteAdvertModal">

            <deleteAdvertModal
                :index="index"
                :advert="advert"
                :adverts="adverts"
                @deleted="handleDeletedAdvert($event)"
                @visibility="isOpenDeleteAdvertModal = $event">
            </deleteAdvertModal>

        </template>

    </Row>

</template>

<script>

    import draggable from 'vuedraggable';
    import idPoptip from './../show/components/idPoptip.vue';
    import namePoptip from './../show/components/namePoptip.vue';
    import endAtPoptip from './../show/components/endAtPoptip.vue';
    import startAtPoptip from './../show/components/startAtPoptip.vue';
    import positionPoptip from './../show/components/positionPoptip.vue';
    import Loader from './../../../components/_common/loaders/default.vue';
    import visibleStatusBadge from './../show/components/visibleStatusBadge.vue';
    import deleteAdvertModal from './components/deleteAdvertModal.vue';
    import basicButton from './../../../components/_common/buttons/basicButton.vue';
    import manageAdvertDrawer from './../show/components/manageAdvertDrawer.vue';

    export default {
        components: {
            draggable, idPoptip, namePoptip, endAtPoptip, startAtPoptip, positionPoptip, Loader,
            visibleStatusBadge, deleteAdvertModal, basicButton, manageAdvertDrawer
        },
        data(){
            return {
                index: null,
                isLoading: false,
                adverts: [],
                advert: null,
                selectedFilters: [],
                arrangeAdverts: false,
                advertsBeforeChanges: [],
                tableColumnsToShowByDefault: [
                    'Selector', 'ID', 'Name', 'Visible', 'Position', 'Start Date', 'End Date'
                ],
                statuses: [
                    {
                        name: 'Visible',
                        desc: 'Adverts on display'
                    },
                    {
                        name: 'Invisible',
                        desc: 'Adverts not on display'
                    },
                    {
                        name: 'Expired',
                        desc: 'Adverts that are expired'
                    }
                ],
                searchWord: '',
                searchTimeout: null,
                isOpenManageAdvertModal: false,
                isOpenDeleteAdvertModal: false,
            }
        },
        watch: {

            /**
             *  Search adverts only 1 second after the user is done typing.
             */
            searchWord: function (val) {

                //  Clear the search timeout variable
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {

                    //  Get the adverts
                    this.fetchAdverts();

                }, 1000); // 1 sec delay
            }
        },
        computed: {
            totalAdverts(){
                return this.adverts.length;
            },
            advertsExist(){
                return this.totalAdverts ? true : false;
            },
            addButtonType(){
                return this.advertsExist ? 'default' : 'success';
            },
            advertsHaveChanged(){

                //  Check if the advert has been modified
                return !_.isEqual(this.adverts, this.advertsBeforeChanges);

            },
            advertsUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:adverts'].href
            },
            advertArrangementUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:advert_arrangement'].href;
            },
            dynamicColumns(){

                var allowedColumns = [];

                //  Advert Selector
                if(this.tableColumnsToShowByDefault.includes('Selector')){
                    allowedColumns.push({
                        type: 'selection',
                        align: 'center',
                        width: 60
                    });
                }

                //  Advert ID
                if(this.tableColumnsToShowByDefault.includes('ID')){
                    allowedColumns.push(
                        {
                            title: 'ID',
                            slot: 'id',
                            width: 100
                        }
                    );
                }

                //  Advert Name
                if(this.tableColumnsToShowByDefault.includes('Name')){
                    allowedColumns.push(
                        {
                            title: 'Name',
                            slot: 'name'
                        }
                    );
                }

                //  Advert Visible
                if(this.tableColumnsToShowByDefault.includes('Visible')){
                    allowedColumns.push(
                        {
                            title: 'Visible',
                            slot: 'visible'
                        }
                    );
                }

                //  Advert Start Date
                if(this.tableColumnsToShowByDefault.includes('Start Date')){
                    allowedColumns.push(
                        {
                            title: 'Start Date',
                            slot: 'start date'
                        }
                    );
                }

                //  Advert End Date
                if(this.tableColumnsToShowByDefault.includes('End Date')){
                    allowedColumns.push(
                        {
                            title: 'End Date',
                            slot: 'end date'
                        }
                    );
                }

                //  Advert Position
                if(this.tableColumnsToShowByDefault.includes('Position')){
                    allowedColumns.push(
                        {
                            title: 'Position',
                            align: 'center',
                            slot: 'position'
                        }
                    );
                }

                //  Action
                allowedColumns.push(
                    {
                        title: 'Action',
                        slot: 'action',
                        width: 80,
                    }
                );

                return allowedColumns;
            }
        },
        methods: {
            handleAddAdvert(){
                this.index = null;
                this.advert = null;
                this.handleOpenManageAdvertModal();
            },
            handleEditAdvert(advert, index){
                this.index = index;
                this.advert = advert;
                this.handleOpenManageAdvertModal();
            },
            handleDeleteAdvert(advert, index){
                this.index = index;
                this.advert = advert;
                this.handleOpenDeleteAdvertModal();
            },
            handleOpenManageAdvertModal(){
                this.isOpenManageAdvertModal = true;
            },
            handleOpenDeleteAdvertModal(){
                this.isOpenDeleteAdvertModal = true;
            },
            handleSavedAdvert(advert){

                //  Re-fetch the adverts
                this.fetchAdverts();

            },
            handleCreatedAdvert(advert){

                //  Re-fetch the adverts
                this.fetchAdverts();

            },
            handleDeletedAdvert(advert){

                //  Re-fetch the adverts
                this.fetchAdverts();

            },
            resetAdverts(){
                this.adverts = _.cloneDeep(this.advertsBeforeChanges);
            },
            copyAdvertsBeforeUpdate(){

                //  Copy adverts before changes
                this.advertsBeforeChanges = _.cloneDeep( this.adverts );

            },
            fetchAdverts() {

                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the adverts url
                    if( this.advertsUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        var statuses = this.selectedFilters.join(',');

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.advertsUrl+'?search='+this.searchWord+'&status='+statuses)
                            .then(({data}) => {

                                //  Get the adverts
                                self.adverts = data['_embedded']['adverts'] || [];

                                //  Turn off any changes detected
                                self.copyAdvertsBeforeUpdate();

                                //  Stop loader
                                self.isLoading = false;

                            })
                            .catch(response => {

                                //  Stop loader
                                self.isLoading = false;

                            });
                    }

                });

            },
            updateAdvertArrangement() {

                //  If we have the advert arrangement url
                if( this.advertArrangementUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    let data = {
                            postData: {
                                arrangements: self.adverts.map((advert, index) => {
                                    return {
                                        "id": advert.id,
                                        "arrangement": (index + 1)
                                    };
                                })
                            }
                        };

                    //  Use the api call() function, refer to api.js
                    return api.call('post', this.advertArrangementUrl, data)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoading = false;

                            //  Swith to table view
                            self.arrangeAdverts = false;

                            //  Get the adverts
                            self.adverts = data['_embedded']['adverts'] || [];

                            //  Turn off any changes detected
                            self.copyAdvertsBeforeUpdate();

                            self.$Message.success({
                                content: 'Adverts updated!',
                                duration: 6
                            });

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoading = false;

                        });
                }

            },
        },
        created(){

            //  Get the adverts
            this.fetchAdverts();

        }
    }
</script>
