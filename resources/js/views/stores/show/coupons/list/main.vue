<template>

    <div>

        <!-- If viewing single coupon -->
        <template v-if="coupon && isViewingCoupon">

            <!-- Single coupon -->
            <singleCoupon :store="store" :location="location" :assignedLocations="assignedLocations"
                          :coupons="coupons" :coupon="coupon" @close="handleCloseCoupon()">
            </singleCoupon>

        </template>

        <!-- If viewing a list of coupons -->
        <Row v-else class="mt-4">

            <Col :span="22" :offset="1">

                <!-- Heading, Add Coupon Button & Watch Video Button -->
                <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                    <Col :span="12">

                        <!-- Heading -->
                        <h1 :class="['font-weight-bold', 'text-muted']">Coupons</h1>

                    </Col>

                    <Col :span="12">

                        <div class="clearfix">

                            <!-- Add Coupon Button -->
                            <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true"
                                        :ripple="!couponsExist && !isLoading" :class="['float-right', 'ml-2']"
                                        :disabled="isLoading" @click.native="handleAddCoupon()">
                                <span>Add Coupon</span>
                            </basicButton>

                            <!-- Watch Video Button -->
                            <Button type="primary" size="default" @click.native="fetchCoupons()" :class="['float-right']">
                                <Icon type="ios-play-outline" class="mr-1" :size="20" />
                                <span>Watch Video</span>
                            </Button>

                        </div>

                    </Col>

                </Row>

                <Card>

                    <!-- Search Bar, Filters, Arrange Coupon Switch, Save Changes Button & Refresh Button -->
                    <Row :gutter="12" class="mb-4">

                        <Col :span="8">

                            <!-- Search Bar -->
                            <Input v-model="searchWord" type="text" size="default" clearable placeholder="Search coupon..." icon="ios-search-outline"></Input>

                        </Col>

                        <Col :span="8">

                            <!-- Filters -->
                            <Poptip trigger="hover" word-wrap class="poptip-w-100">

                                <div slot="content">
                                    <span v-if="filterDesc" :class="['font-italic']">{{ filterDesc }}</span>
                                    <span v-else>Add filters for specific coupons</span>
                                </div>


                                <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                        prefix="ios-funnel-outline" clearable placeholder="Add filters"
                                        @on-select="handleSelectedFilters()" @on-clear="clearfilterDesc()">

                                    <!-- Filter Options-->
                                    <Option v-for="(status, index) in statuses" :value="status.name" :key="index" :label="status.name"
                                            @mouseover.native="setFilterDesc(status.desc)"
                                            @mouseout.native="clearfilterDesc()">
                                        <span :class="['font-weight-bold']">{{ status.name }}</span>
                                    </Option>

                                </Select>

                            </Poptip>

                        </Col>

                        <Col :span="8" :class="['clearfix']">

                            <!-- Refresh Button -->
                            <Button type="default" size="default" :class="['float-right']"
                                :loading="isLoading" :disabled="isLoading"
                                @click.native="fetchCoupons()">
                                <Icon v-show="!isLoading" type="ios-refresh" class="mr-1" :size="20" />
                                <span>Refresh</span>
                            </Button>

                        </Col>

                    </Row>

                    <!-- Coupon Table -->
                    <Table :columns="dynamicColumns" :data="coupons"
                            :loading="isLoading" no-data-text="No coupons found" :style="{ overflow: 'visible' }">

                        <!-- Name Poptip -->
                        <namePoptip slot-scope="{ row, index }" slot="name" :coupon="row"></namePoptip>

                        <!-- Active Status Poptip -->
                        <activeStatusBadge slot-scope="{ row, index }" slot="active" :coupon="row"></activeStatusBadge>

                        <template slot-scope="{ row, index }" slot="action">

                            <div>
                                <Dropdown trigger="click" placement="bottom-end">
                                    <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                                    <DropdownMenu slot="list">
                                        <DropdownItem name="View" @click.native="handleViewCoupon(row, index)">View</DropdownItem>
                                        <DropdownItem name="Edit" @click.native="handleEditCoupon(row, index)">Edit</DropdownItem>
                                        <DropdownItem name="Delete" class="text-danger" @click.native="handleDeleteCoupon(row, index)">Delete</DropdownItem>
                                    </DropdownMenu>
                                </Dropdown>
                            </div>

                        </template>

                    </Table>

                </Card>

                <!--
                    MODAL TO CREATE / EDIT INSTANT CART
                -->
                <template v-if="isOpenManageCouponModal">

                    <manageCouponDrawer
                        :index="index"
                        :store="store"
                        :coupon="coupon"
                        :location="location"
                        :layoutSize="layoutSize"
                        :assignedLocations="assignedLocations"
                        @savedCoupon="handleSavedCoupon($event)"
                        @createdCoupon="handleCreatedCoupon($event)"
                        @visibility="isOpenManageCouponModal = $event">
                    </manageCouponDrawer>

                </template>

                <!--
                    MODAL TO DELETE INSTANT CART
                -->
                <template v-if="isOpenDeleteCouponModal">

                    <deleteCouponModal
                        :index="index"
                        :coupon="coupon"
                        :coupons="coupons"
                        @deleted="handleDeletedCoupon($event)"
                        @visibility="isOpenDeleteCouponModal = $event">
                    </deleteCouponModal>

                </template>

            </Col>

        </Row>

    </div>

</template>

<script>

    import namePoptip from './../show/components/namePoptip.vue';
    import deleteCouponModal from './../components/deleteCouponModal.vue';
    import manageCouponDrawer from './../components/manageCouponDrawer.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import activeStatusBadge from './../show/components/activeStatusBadge.vue';
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';

    export default {
        mixins: [ miscMixin ],
        components: {
            namePoptip, deleteCouponModal, manageCouponDrawer, activeStatusBadge, basicButton
        },
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            assignedLocations: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        data(){
            return {
                isOpenDeleteCouponModal: false,
                isOpenManageCouponModal: false,
                isViewingCoupon: false,
                isLoading: false,
                layoutSize: null,
                filterDesc: '',
                coupon: null,
                coupons: [],
                index: null,
                tableColumnsToShowByDefault: [
                    'Selector', 'Name', 'Active', 'Created Date'
                ],
                statuses: [
                    {
                        name: 'Active',
                        desc: 'Coupons that are available'
                    },
                    {
                        name: 'Inactive',
                        desc: 'Coupons that are not available'
                    },
                    {
                        name: 'Fixed rate',
                        desc: 'Coupons that discount by fixed rate'
                    },
                    {
                        name: 'Percentage rate',
                        desc: 'Coupons that discount by percentage rate'
                    },
                    {
                        name: 'Total Amount',
                        desc: 'Coupons that discount by total amount of cart'
                    },
                    {
                        name: 'Total Items',
                        desc: 'Coupons that discount by total items in cart'
                    },
                    {
                        name: 'Total Unique Items',
                        desc: 'Coupons that discount by total unique items in cart'
                    },
                    {
                        name: 'Free delivery',
                        desc: 'Coupons that offer free delivery'
                    }
                ],
                selectedFilters: ['Active'],
                searchTimeout: null,
                searchWord: '',
            }
        },
        watch: {

            /**
             *  Search coupons only 1 second after the user is done typing.
             */
            searchWord: function (val) {

                //  Clear the search timeout variable
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {

                    //  Get the coupons
                    this.fetchCoupons();

                }, 1000); // 1 sec delay
            }

        },
        computed: {
            totalCoupons(){
                return this.coupons.length;
            },
            couponsExist(){
                return this.totalCoupons ? true : false;
            },
            addButtonType(){
                return this.couponsExist ? 'default' : 'success';
            },
            couponsUrl(){
                return this.location['_links']['bos:coupons'].href;
            },
            dynamicColumns(){

                var allowedColumns = [];

                //  Coupon Selector
                if(this.tableColumnsToShowByDefault.includes('Selector')){
                    allowedColumns.push({
                        type: 'selection',
                        align: 'center',
                        width: 60
                    });
                }

                //  Coupon Name
                if(this.tableColumnsToShowByDefault.includes('Name')){
                    allowedColumns.push(
                        {
                            title: 'Name',
                            slot: 'name',
                            width: 200
                        }
                    );
                }

                //  Coupon Active
                if(this.tableColumnsToShowByDefault.includes('Active')){
                    allowedColumns.push(
                        {
                            title: 'Active',
                            slot: 'active',
                            width: 100
                        }
                    );
                }

                //  Created Date
                if(this.tableColumnsToShowByDefault.includes('Created Date')){
                    allowedColumns.push(
                    {
                        title: 'Date',
                        sortable: true,
                        render: (h, params) => {
                            return h('Poptip', {
                                style: {
                                    width: '100%',
                                    textAlign:'left'
                                },
                                props: {
                                    width: 280,
                                    wordWrap: true,
                                    trigger:'hover',
                                    placement: 'top',
                                    content: 'Date: '+ this.formatDateTime(params.row.created_at.date, true)
                                }
                            }, [
                                h('span', this.formatDateTime(params.row.created_at.date))
                            ])
                        }
                    })
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
            },
        },
        methods: {
            handleSelectedFilters(){
                this.clearfilterDesc();
                this.fetchCoupons();
            },
            clearfilterDesc(){
                this.filterDesc = '';
            },
            setFilterDesc(description){
                this.filterDesc = description;
            },
            handleAddCoupon(){
                this.index = null;
                this.coupon = null;
                this.layoutSize = 'small';
                this.handleOpenManageCouponModal();
            },
            handleEditCoupon(coupon, index){
                this.index = index;
                this.coupon = coupon;
                this.layoutSize = 'small';
                this.handleOpenManageCouponModal();
            },
            handleViewCoupon(coupon, index){
                this.index = index;
                this.coupon = coupon;
                this.layoutSize = 'large';
                this.isViewingCoupon = true;
            },
            handleDeleteCoupon(coupon, index){
                this.index = index;
                this.coupon = coupon;
                this.handleOpenDeleteStoreModal();
            },
            handleOpenManageCouponModal(){
                this.isOpenManageCouponModal = true;
            },
            handleOpenDeleteStoreModal(){
                this.isOpenDeleteCouponModal = true;
            },
            handleCloseCoupon(){
                this.isViewingCoupon = false;
            },
            handleCreatedCoupon(coupon){

                //  Add the new created coupon to the top of the list
                this.coupons.unshift(coupon);

            },
            handleDeletedCoupon(){

                this.fetchCoupons();

            },
            handleSavedCoupon(coupon){

                //  Update the coupon
                this.$set(this.coupons, this.index, coupon);

            },
            fetchCoupons() {
                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the coupons url
                    if( this.couponsUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        var statuses = this.selectedFilters.join(',');

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.couponsUrl+'?search='+this.searchWord+'&status='+statuses)
                            .then(({data}) => {

                                //  Get the coupons
                                self.coupons = data['_embedded']['coupons'] || [];

                                //  Stop loader
                                self.isLoading = false;

                            })
                            .catch(response => {

                                //  Stop loader
                                self.isLoading = false;

                            });
                    }

                });

            }
        },
        created(){

            //  Get the location coupons
            this.fetchCoupons();

        }
    };

</script>
