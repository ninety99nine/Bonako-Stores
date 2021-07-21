<template>

    <Row :gutter="12">

        <Col :span="20" :offset="2">

            <!-- Heading, Add Store Button & Watch Video Button -->
            <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                <Col :span="12">

                    <!-- Heading -->
                    <h1 :class="['font-weight-bold', 'text-muted']">Popular Stores</h1>

                </Col>

                <Col :span="12">

                    <div class="clearfix">

                        <!-- Add Popular Store Button -->
                        <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true"
                                    :ripple="!popularStoresExist && !isLoading" :class="['float-right', 'ml-2']"
                                    :disabled="(popularStoresExist && popularStoresHaveChanged) || isLoading"
                                    @click.native="handleAddPopularStore()">
                            <span>Add Store</span>
                        </basicButton>

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" @click.native="fetchPopularStores()" :class="['float-right']">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

            </Row>

            <!-- Search Bar, Filters, Arrange Popular Stores Switch, Save Changes Button & Refresh Button -->
            <Row :gutter="12" class="mb-4">

                <Col :span="8">

                    <!-- Search Bar -->
                    <Input v-model="searchWord" type="text" size="default" :disabled="isLoading || arrangePopularStores" clearable placeholder="Search popular store name..." icon="ios-search-outline"></Input>

                </Col>

                <Col :span="8">

                    <!-- Filters -->
                    <Poptip trigger="hover" content="Add filters for popular stores" word-wrap class="poptip-w-100">
                        <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                prefix="ios-funnel-outline" clearable placeholder="Add filters"
                                :disabled="isLoading || arrangePopularStores" @on-select="fetchPopularStores()">

                            <!-- Filter Options-->
                            <Option v-for="(status, index) in statuses" :value="status.name" :key="index" :label="status.name">
                                <span :class="['font-weight-bold']">{{ status.name }}</span>
                                <span v-if="status.desc" style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">{{ status.desc }}</span>
                            </Option>

                        </Select>
                    </Poptip>

                </Col>

                <Col :span="8" :class="['clearfix']">

                    <!-- Arrange Popular Stores Switch -->
                    <div v-if="!popularStoresHaveChanged" :class="['float-left', 'mt-1', 'ml-3']">
                        <span :style="{ width: '200px' }" class="font-weight-bold">Arrange Stores: </span>
                        <Poptip trigger="hover" word-wrap width="300" content="Turn on to drag and drop and change the arrangement of popular stores">
                            <i-Switch v-model="arrangePopularStores" :disabled="!popularStores.length || isLoading || popularStoresHaveChanged" />
                        </Poptip>
                    </div>

                    <template v-if="popularStores.length && popularStoresHaveChanged">

                        <!-- Save Changes Button -->
                        <basicButton type="success" size="default"
                                    :class="['float-right', 'ml-2']" :ripple="popularStoresHaveChanged && !isLoading"
                                    :disabled="isLoading" :loading="isLoading"
                                    @click.native="updatePopularStoreArrangement()">
                            <span>Save Changes</span>
                        </basicButton>

                        <Button v-if="popularStoresHaveChanged" type="default"
                                size="default" :class="['float-right']"
                                @click.native="resetPopularStores()"
                                :disabled="isLoading">
                            <span>Cancel</span>
                        </Button>

                    </template>

                    <!-- Refresh Button -->
                    <Button v-else type="default" size="default" :class="['float-right']"
                        :loading="isLoading" :disabled="isLoading"
                        @click.native="fetchPopularStores()">
                        <Icon v-show="!isLoading" type="ios-refresh" class="mr-1" :size="20" />
                        <span>Refresh</span>
                    </Button>

                </Col>

            </Row>


            <!-- Product Table -->
            <Table v-show="!arrangePopularStores" :columns="dynamicColumns" :class="['w-100']"
                    :data="popularStores" :loading="isLoading" no-data-text="No popular stores found"
                    :style="{ overflow: 'visible' }">

                <!-- ID Poptip -->
                <idPoptip slot-scope="{ row, index }" slot="id" :popularStore="row"></idPoptip>

                <!-- Name Poptip -->
                <namePoptip slot-scope="{ row, index }" slot="name" :popularStore="row"></namePoptip>

                <!-- Visible Poptip -->
                <visibleStatusBadge slot-scope="{ row, index }" slot="visible" :popularStore="row"></visibleStatusBadge>

                <!-- Start At Poptip -->
                <startAtPoptip slot-scope="{ row, index }" slot="start date" :popularStore="row"></startAtPoptip>

                <!-- End At Poptip -->
                <endAtPoptip slot-scope="{ row, index }" slot="end date" :popularStore="row"></endAtPoptip>

                <!-- Position Poptip -->
                <positionPoptip slot-scope="{ row, index }" slot="position" :popularStore="row"></positionPoptip>

                <template slot-scope="{ row, index }" slot="action">

                    <div>
                        <Dropdown trigger="click" placement="bottom-end">
                            <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                            <DropdownMenu slot="list">
                                <DropdownItem name="Edit" @click.native="handleEditPopularStore(row, index)">Edit</DropdownItem>
                                <DropdownItem name="Remove" class="text-danger" @click.native="handleDeletePopularStore(row, index)">Remove</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </div>

                </template>

            </Table>

            <!-- Draggable Product Cards (Arrange Popular Stores) -->
            <draggable v-show="arrangePopularStores" v-model="popularStores" draggable=".draggable-popular-store">

                <Card v-for="(popularStore, index) in popularStores" :key="index" :class="['draggable-popular-store', 'cursor-pointer', 'mb-1']">

                    <div :class="['clearfix']">

                        <!-- Product Name  -->
                        <span :class="['float-left', 'font-weight-bold']">{{ getStoreName(popularStore) }}</span>

                        <!-- Move Product Button  -->
                        <Icon :class="['float-right', 'dragger-handle']" type="ios-move" size="20" />

                        <!-- Product Position  -->
                        <span :class="['float-right', 'border-bottom', 'mr-5']"># {{ (index + 1) }}</span>

                    </div>

                </Card>

            </draggable>

        </Col>

        <!--
            MODAL TO CREATE / EDIT POPULAR STORE
        -->
        <template v-if="isOpenManagePopularStoreModal">

            <managePopularStoreDrawer
                :index="index"
                :popularStore="popularStore"
                :popularStores="popularStores"
                @savedPopularStore="handleSavedPopularStore($event)"
                @createdPopularStore="handleCreatedPopularStore($event)"
                @visibility="isOpenManagePopularStoreModal = $event">
            </managePopularStoreDrawer>

        </template>

        <!--
            MODAL TO DELETE POPULAR STORE
        -->
        <template v-if="isOpendeletePopularStoreModal">

            <deletePopularStoreModal
                :index="index"
                :popularStore="popularStore"
                :popularStores="popularStores"
                @deleted="handleDeletedPopularStore($event)"
                @visibility="isOpendeletePopularStoreModal = $event">
            </deletePopularStoreModal>

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
    import deletePopularStoreModal from './components/deletePopularStoreModal.vue';
    import basicButton from './../../../components/_common/buttons/basicButton.vue';
    import managePopularStoreDrawer from './../show/components/managePopularStoreDrawer.vue';

    export default {
        components: {
            draggable, idPoptip, namePoptip, endAtPoptip, startAtPoptip, positionPoptip, Loader,
            visibleStatusBadge, deletePopularStoreModal, basicButton, managePopularStoreDrawer
        },
        data(){
            return {
                index: null,
                isLoading: false,
                popularStores: [],
                popularStore: null,
                selectedFilters: [],
                arrangePopularStores: false,
                popularStoresBeforeChanges: [],
                tableColumnsToShowByDefault: [
                    'Selector', 'ID', 'Name', 'Visible', 'Position', 'Start Date', 'End Date'
                ],
                statuses: [
                    {
                        name: 'Visible',
                        desc: 'Popular stores on display'
                    },
                    {
                        name: 'Invisible',
                        desc: 'Popular stores not on display'
                    },
                    {
                        name: 'Expired',
                        desc: 'Popular stores that are expired'
                    }
                ],
                searchWord: '',
                searchTimeout: null,
                isOpenManagePopularStoreModal: false,
                isOpendeletePopularStoreModal: false,
            }
        },
        watch: {

            /**
             *  Search popular stores only 1 second after the user is done typing.
             */
            searchWord: function (val) {

                //  Clear the search timeout variable
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {

                    //  Get the popular stores
                    this.fetchPopularStores();

                }, 1000); // 1 sec delay
            }
        },
        computed: {
            totalPopularStores(){
                return this.popularStores.length;
            },
            popularStoresExist(){
                return this.totalPopularStores ? true : false;
            },
            addButtonType(){
                return this.popularStoresExist ? 'default' : 'success';
            },
            popularStoresHaveChanged(){

                //  Check if the popularStore has been modified
                return !_.isEqual(this.popularStores, this.popularStoresBeforeChanges);

            },
            popularStoresUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:popular_stores'].href
            },
            popularStoreArrangementUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:popular_store_arrangement'].href;
            },
            dynamicColumns(){

                var allowedColumns = [];

                //  Popular Store Selector
                if(this.tableColumnsToShowByDefault.includes('Selector')){
                    allowedColumns.push({
                        type: 'selection',
                        align: 'center',
                        width: 60
                    });
                }

                //  Popular Store ID
                if(this.tableColumnsToShowByDefault.includes('ID')){
                    allowedColumns.push(
                        {
                            title: 'ID',
                            slot: 'id',
                            width: 100
                        }
                    );
                }

                //  Popular Store Name
                if(this.tableColumnsToShowByDefault.includes('Name')){
                    allowedColumns.push(
                        {
                            title: 'Name',
                            slot: 'name'
                        }
                    );
                }

                //  Popular Store Visible
                if(this.tableColumnsToShowByDefault.includes('Visible')){
                    allowedColumns.push(
                        {
                            title: 'Visible',
                            slot: 'visible'
                        }
                    );
                }

                //  Popular Store Start Date
                if(this.tableColumnsToShowByDefault.includes('Start Date')){
                    allowedColumns.push(
                        {
                            title: 'Start Date',
                            slot: 'start date'
                        }
                    );
                }

                //  Popular Store End Date
                if(this.tableColumnsToShowByDefault.includes('End Date')){
                    allowedColumns.push(
                        {
                            title: 'End Date',
                            slot: 'end date'
                        }
                    );
                }

                //  Popular Store Position
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
            getStoreName(popularStore){
                return ((popularStore._embedded || {}).store || {}).name;
            },
            handleAddPopularStore(){
                this.index = null;
                this.popularStore = null;
                this.handleOpenManagePopularStoreModal();
            },
            handleEditPopularStore(popularStore, index){
                this.index = index;
                this.popularStore = popularStore;
                this.handleOpenManagePopularStoreModal();
            },
            handleDeletePopularStore(popularStore, index){
                this.index = index;
                this.popularStore = popularStore;
                this.handleOpenDeleteStoreModal();
            },
            handleOpenManagePopularStoreModal(){
                this.isOpenManagePopularStoreModal = true;
            },
            handleOpenDeleteStoreModal(){
                this.isOpendeletePopularStoreModal = true;
            },
            handleSavedPopularStore(popularStore){

                //  Re-fetch the popular stores
                this.fetchPopularStores();

            },
            handleCreatedPopularStore(popularStore){

                //  Re-fetch the popular stores
                this.fetchPopularStores();

            },
            handleDeletedPopularStore(popularStore){

                //  Re-fetch the popular stores
                this.fetchPopularStores();

            },
            resetPopularStores(){
                this.popularStores = _.cloneDeep(this.popularStoresBeforeChanges);
            },
            copyPopularStoresBeforeUpdate(){

                //  Copy popular stores before changes
                this.popularStoresBeforeChanges = _.cloneDeep( this.popularStores );

            },
            fetchPopularStores() {
                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the popular stores url
                    if( this.popularStoresUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        var statuses = this.selectedFilters.join(',');

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.popularStoresUrl+'?search='+this.searchWord+'&status='+statuses)
                            .then(({data}) => {

                                //  Get the popular stores
                                self.popularStores = data['_embedded']['popular_stores'] || [];

                                //  Turn off any changes detected
                                self.copyPopularStoresBeforeUpdate();

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
            updatePopularStoreArrangement() {

                //  If we have the popular store arrangement url
                if( this.popularStoreArrangementUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    let data = {
                            postData: {
                                arrangements: self.popularStores.map((popularStore, index) => {
                                    return {
                                        "id": popularStore.id,
                                        "arrangement": (index + 1)
                                    };
                                })
                            }
                        };

                    //  Use the api call() function, refer to api.js
                    return api.call('post', this.popularStoreArrangementUrl, data)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoading = false;

                            //  Swith to table view
                            self.arrangePopularStores = false;

                            //  Set the popular stores
                            self.popularStores = data['_embedded']['popular_stores'] || [];

                            //  Turn off any changes detected
                            self.copyPopularStoresBeforeUpdate();

                            self.$Message.success({
                                content: 'Popular stores updated!',
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

            //  Get the popular stores
            this.fetchPopularStores();

        }
    }
</script>
