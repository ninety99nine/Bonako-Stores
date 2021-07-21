<template>

    <div>

        <!-- If viewing single instant cart -->
        <template v-if="instantCart && isViewingInstantCart">

            <!-- Single instant cart -->
            <singleInstantCart :store="store" :location="location" :assignedLocations="assignedLocations"
                               :instantCarts="instantCarts" :instantCart="instantCart"
                               @close="handleCloseInstantCart()">
            </singleInstantCart>

        </template>

        <!-- If viewing a list of instant carts -->
        <Row v-else class="mt-4">

            <Col :span="22" :offset="1">

                <!-- Heading, Add Instant Cart Button & Watch Video Button -->
                <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                    <Col :span="12">

                        <!-- Heading -->
                        <h1 :class="['font-weight-bold', 'text-muted']">Instant Carts</h1>

                    </Col>

                    <Col :span="12">

                        <div class="clearfix">

                            <!-- Add Instant Cart Button -->
                            <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true"
                                        :ripple="!instantCartsExist && !isLoading" :class="['float-right', 'ml-2']"
                                        :disabled="isLoading" @click.native="handleAddInstantCart()">
                                <span>Add Instant Cart</span>
                            </basicButton>

                            <!-- Watch Video Button -->
                            <Button type="primary" size="default" @click.native="fetchInstantCarts()" :class="['float-right']">
                                <Icon type="ios-play-outline" class="mr-1" :size="20" />
                                <span>Watch Video</span>
                            </Button>

                        </div>

                    </Col>

                </Row>

                <Card>

                    <!-- Navigation Tabs -->
                    <Tabs v-model="activeNavTab" type="card" :style="{ overflow: 'visible' }" :animated="false" class="mb-2">

                        <TabPane v-for="(currentTabName, key) in navTabs" :key="key"
                                :label="currentTabName.name" :name="currentTabName.value">
                        </TabPane>

                    </Tabs>

                    <!-- Search Bar, Filters, Arrange Instant Cart Switch, Save Changes Button & Refresh Button -->
                    <Row :gutter="12" class="mb-4">

                        <Col :span="8">

                            <!-- Search Bar -->
                            <Input v-model="searchWord" type="text" size="default" clearable placeholder="Search instant cart..." icon="ios-search-outline"></Input>

                        </Col>

                        <Col :span="8">

                            <!-- Filters -->
                            <Poptip trigger="hover" content="Add filters for specific instant carts" word-wrap class="poptip-w-100">
                                <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                        prefix="ios-funnel-outline" clearable placeholder="Add filters"
                                        :disabled="activeNavTab == '2'"
                                        @on-select="fetchInstantCarts()">

                                    <!-- Filter Options-->
                                    <Option v-for="(status, index) in statuses" :value="status.name" :key="index" :label="status.name">
                                        <span :class="['font-weight-bold']">{{ status.name }}</span>
                                        <span v-if="status.desc" style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">{{ status.desc }}</span>
                                    </Option>

                                </Select>
                            </Poptip>

                        </Col>

                        <Col :span="8" :class="['clearfix']">

                            <!-- Refresh Button -->
                            <Button type="default" size="default" :class="['float-right']"
                                :loading="isLoading" :disabled="isLoading"
                                @click.native="fetchInstantCarts()">
                                <Icon v-show="!isLoading" type="ios-refresh" class="mr-1" :size="20" />
                                <span>Refresh</span>
                            </Button>

                        </Col>

                    </Row>

                    <!-- Paid Instant Carts -->
                    <div v-show="activeNavTab == 1">

                        <!-- Instant Cart Table -->
                        <Table class="instant-cart-table" :columns="dynamicColumns" :data="instantCarts"
                                :loading="isLoading" no-data-text="No instant carts found" :style="{ overflow: 'visible' }">

                            <!-- ID Poptip -->
                            <idPoptip slot-scope="{ row, index }" slot="id" :instantCart="row"></idPoptip>

                            <!-- Name Poptip -->
                            <namePoptip slot-scope="{ row, index }" slot="name" :instantCart="row"></namePoptip>

                            <!-- Active Status Poptip -->
                            <activeStatusBadge slot-scope="{ row, index }" slot="active" :instantCart="row"></activeStatusBadge>

                            <!-- Cart Items Poptip -->
                            <cartItems slot-scope="{ row, index }" slot="items" :cart="(row._embedded || {}).cart"></cartItems>

                            <!-- Cart Coupons Poptip -->
                            <cartCoupons slot-scope="{ row, index }" slot="coupons" :cart="(row._embedded || {}).cart"></cartCoupons>

                            <!-- Stock Poptip -->
                            <stockPoptip slot-scope="{ row, index }" slot="stock" :instantCart="row"></stockPoptip>

                            <!-- Cart Total Poptip -->
                            <cartPricing slot-scope="{ row, index }" slot="total" :cart="(row._embedded || {}).cart"></cartPricing>

                            <!-- Visit Short Code Dialing Code Poptip -->
                            <visitShortCodeDialingCodePoptip slot-scope="{ row, index }" slot="shortcode" :instantCart="row"></visitShortCodeDialingCodePoptip>

                            <!-- Visit Short Code Expiry Time Poptip -->
                            <visitShortCodeExpiryTimePoptip slot-scope="{ row, index }" slot="expiry" :instantCart="row"></visitShortCodeExpiryTimePoptip>

                            <template slot-scope="{ row, index }" slot="action">

                                <div>
                                    <Dropdown trigger="click" placement="bottom-end">
                                        <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                                        <DropdownMenu slot="list">
                                            <DropdownItem name="View" @click.native="handleViewInstantCart(row, index)">View</DropdownItem>
                                            <DropdownItem name="Edit" @click.native="handleEditInstantCart(row, index)">Edit</DropdownItem>
                                            <DropdownItem name="Delete" class="text-danger" @click.native="handleDeleteInstantCart(row, index)">Delete</DropdownItem>
                                        </DropdownMenu>
                                    </Dropdown>
                                </div>

                            </template>

                        </Table>

                    </div>

                    <!-- Unpaid Instant Carts -->
                    <div v-show="activeNavTab == 2">

                        <!-- Pay To Activate Heading -->
                        <Divider orientation="left" class="font-weight-bold mt-4">Pay To Activate</Divider>

                        <Card v-for="(instantCart, index) in instantCarts" :key="index" :class="['mb-1']"
                              :style="{ background: 'mintcream', borderLeft: '5px solid aquamarine' }">

                            <div :class="['clearfix']">

                                <!-- Instant Cart Name  -->
                                <span :class="['float-left', 'font-weight-bold']">{{ instantCart.name }}</span>

                                <!-- Dial to pay -->
                                <div :class="['float-right']">

                                    <dialToPay :resource="instantCart" name="instant cart" description="Pay to activate instant cart"
                                               placement="top-end" :dialToPayClass="['float-right']" :countdownClass="[]"
                                               @updated="handlePaymentShortCodeUpdate()">
                                    </dialToPay>

                                </div>

                            </div>

                        </Card>

                        <!-- If we are loading, Show Loader -->
                        <Loader v-if="isLoading" class="mt-2"></Loader>

                        <!-- If we don't have any instant cart for that require payment -->
                        <Alert v-if="(!isLoading && !instantCarts.length)" type="warning" show-icon>
                            No instant carts to pay
                            <span :class="['mx-1']">-</span>
                            <span :class="['btn-link cursor-pointer']" @click="handleAddInstantCart()">Add Instant Cart</span>
                        </Alert>

                    </div>

                </Card>

                <!--
                    MODAL TO CREATE / EDIT INSTANT CART
                -->
                <template v-if="isOpenManageInstantCartModal">

                    <manageInstantCartDrawer
                        :index="index"
                        :store="store"
                        :location="location"
                        :layoutSize="layoutSize"
                        :instantCart="instantCart"
                        :assignedLocations="assignedLocations"
                        @savedInstantCart="handleSavedInstantCart($event)"
                        @createdInstantCart="handleCreatedInstantCart($event)"
                        @visibility="isOpenManageInstantCartModal = $event">
                    </manageInstantCartDrawer>

                </template>

                <!--
                    MODAL TO DELETE INSTANT CART
                -->
                <template v-if="isOpenDeleteInstantCartModal">

                    <deleteInstantCartModal
                        :index="index"
                        :instantCart="instantCart"
                        :instantCarts="instantCarts"
                        @deleted="handleDeletedInstantCart($event)"
                        @visibility="isOpenDeleteInstantCartModal = $event">
                    </deleteInstantCartModal>

                </template>

            </Col>

        </Row>

    </div>

</template>

<script>

    import idPoptip from './../show/components/idPoptip.vue';
    import dialToPay from './../../../../payment/dialToPay.vue';
    import namePoptip from './../show/components/namePoptip.vue';
    import stockPoptip from './../show/components/stockPoptip.vue';
    import cartItems from './../../carts/show/components/cartItems.vue';
    import cartCoupons from './../../carts/show/components/cartCoupons.vue';
    import cartPricing from './../../carts/show/components/cartPricing.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import activeStatusBadge from './../show/components/activeStatusBadge.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import deleteInstantCartModal from './../components/deleteInstantCartModal.vue';
    import manageInstantCartDrawer from './../components/manageInstantCartDrawer.vue';
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';
    import visitShortCodeExpiryTimePoptip from './../show/components/visitShortCodeExpiryTimePoptip.vue';
    import visitShortCodeDialingCodePoptip from './../show/components/visitShortCodeDialingCodePoptip.vue';

    export default {
        mixins: [ miscMixin ],
        components: {
            idPoptip, dialToPay, namePoptip, stockPoptip, cartItems, cartCoupons, cartPricing, activeStatusBadge, Loader,
            visitShortCodeExpiryTimePoptip, visitShortCodeDialingCodePoptip, deleteInstantCartModal,
            manageInstantCartDrawer, basicButton
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
                isOpenDeleteInstantCartModal: false,
                isOpenManageInstantCartModal: false,
                isViewingInstantCart: false,
                instantCart: null,
                layoutSize: null,
                isLoading: false,
                instantCarts: [],
                activeNavTab: '1',
                index: null,
                navTabs: [
                    {
                        name: 'Paid Carts',
                        value: '1',
                    },
                    {
                        name: 'Unpaid Carts',
                        value: '2'
                    }
                ],
                tableColumnsToShowByDefault: [
                    'Selector', 'ID', 'Name', 'Stock', 'Active', 'Items', 'Coupons', 'Total', 'Dialing Code', 'Expiry Date'
                ],
                statuses: [
                    {
                        name: 'Active',
                        desc: 'Instant carts that are available'
                    },
                    {
                        name: 'Inactive',
                        desc: 'Instant carts that are not available'
                    },
                    {
                        name: 'Expired',
                        desc: 'Instant carts that are expired'
                    },
                    {
                        name: 'Free delivery',
                        desc: 'Instant carts that offer free delivery'
                    }
                ],
                beforeNavigationSelectedFilters: [],
                selectedFilters: ['Active'],
                searchTimeout: null,
                searchWord: '',
            }
        },
        watch: {

            /**
             *  Search instant carts only 1 second after the user is done typing.
             */
            searchWord: function (val) {

                //  Clear the search timeout variable
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {

                    //  Get the instant carts
                    this.fetchInstantCarts();

                }, 1000); // 1 sec delay
            },

            //  Watch for changes on the activeNavTab
            activeNavTab: {
                handler: function (val, oldVal) {

                    if( val === '1' ){

                        this.selectedFilters = this.beforeNavigationSelectedFilters;

                    }else{

                        this.beforeNavigationSelectedFilters = this.selectedFilters;

                        this.selectedFilters = ['Expired'];

                    }

                    //  Start loader
                    this.isLoading = true;

                    //  Empty instant carts
                    this.instantCarts = [];

                    //  Refresh isntant carts
                    this.fetchInstantCarts();

                }
            }

        },
        computed: {
            totalInstantCarts(){
                return this.instantCarts.length;
            },
            instantCartsExist(){
                return this.totalInstantCarts ? true : false;
            },
            addButtonType(){
                return this.instantCartsExist ? 'default' : 'success';
            },
            instantCartsUrl(){
                return this.location['_links']['bos:instant_carts'].href;
            },
            dynamicColumns(){

                var allowedColumns = [];

                //  Instant Cart Selector
                if(this.tableColumnsToShowByDefault.includes('Selector')){
                    allowedColumns.push({
                        type: 'selection',
                        align: 'center',
                        width: 60
                    });
                }

                //  Instant Cart ID
                if(this.tableColumnsToShowByDefault.includes('ID')){
                    allowedColumns.push(
                        {
                            title: 'ID',
                            slot: 'id',
                            width: 100
                        }
                    );
                }

                //  Instant Cart Name
                if(this.tableColumnsToShowByDefault.includes('Name')){
                    allowedColumns.push(
                        {
                            title: 'Name',
                            slot: 'name',
                            width: 200
                        }
                    );
                }

                //  Instant Cart Active
                if(this.tableColumnsToShowByDefault.includes('Active')){
                    allowedColumns.push(
                        {
                            title: 'Active',
                            slot: 'active',
                            width: 100
                        }
                    );
                }

                //  Cart Items
                if(this.tableColumnsToShowByDefault.includes('Items')){
                    allowedColumns.push(
                        {
                            title: 'Items',
                            slot: 'items',
                            align: 'center',
                            width: 100
                        }
                    );
                }

                //  Cart Coupons
                if(this.tableColumnsToShowByDefault.includes('Coupons')){
                    allowedColumns.push(
                        {
                            title: 'Coupons',
                            slot: 'coupons',
                            align: 'center',
                            width: 100
                        }
                    );
                }

                //  Stock
                if(this.tableColumnsToShowByDefault.includes('Stock')){
                    allowedColumns.push(
                        {
                            title: 'Stock',
                            slot: 'stock',
                            width: 150,
                        }
                    );
                }

                //  Cart Grand Total
                if(this.tableColumnsToShowByDefault.includes('Total')){
                    allowedColumns.push(
                        {
                            title: 'Total',
                            slot: 'total',
                            width: 100
                        }
                    );
                }

                //  Instant Cart Dialing Code
                if(this.tableColumnsToShowByDefault.includes('Dialing Code')){
                    allowedColumns.push(
                        {
                            title: 'Shortcode',
                            slot: 'shortcode',
                            width: 110
                        }
                    );
                }

                //  Instant Cart Expiry Date
                if(this.tableColumnsToShowByDefault.includes('Expiry Date')){
                    allowedColumns.push(
                        {
                            title: 'Expiry',
                            slot: 'expiry',
                            width: 180,
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
            handleAddInstantCart(){
                this.index = null;
                this.instantCart = null;
                this.layoutSize = 'small';
                this.handleOpenManageInstantCartModal();
            },
            handleEditInstantCart(instantCart, index){
                this.index = index;
                this.instantCart = instantCart;
                this.layoutSize = 'small';
                this.handleOpenManageInstantCartModal();
            },
            handleViewInstantCart(instantCart, index){
                this.index = index;
                this.instantCart = instantCart;
                this.layoutSize = 'large';
                this.isViewingInstantCart = true;
            },
            handleDeleteInstantCart(instantCart, index){
                this.index = index;
                this.instantCart = instantCart;
                this.handleOpenDeleteStoreModal();
            },
            handleOpenManageInstantCartModal(){
                this.isOpenManageInstantCartModal = true;
            },
            handleOpenDeleteStoreModal(){
                this.isOpenDeleteInstantCartModal = true;
            },
            handleCloseInstantCart(){
                this.isViewingInstantCart = false;
            },
            handleCreatedInstantCart(instantCart){

                //  Add the new created instantCart to the top of the list
                this.instantCarts.unshift(instantCart);

                //  Navigate to "Unpaid" instant carts (Watcher will force a refresh on activeNavTab change)
                this.activeNavTab = '2';

                //  Re-calculate the totals
                this.$emit('fetchLocationTotals');

            },
            handleDeletedInstantCart(){

                this.fetchInstantCarts();

                //  Re-calculate the totals
                this.$emit('fetchLocationTotals');

            },
            handleSavedInstantCart(instantCart){

                //  Update the instant cart
                this.$set(this.instantCarts, this.index, instantCart);

            },
            handlePaymentShortCodeUpdate(){

                //  Refetch the instant carts
                this.fetchInstantCarts();

            },
            fetchInstantCarts() {
                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the instant carts url
                    if( this.instantCartsUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        var statuses = this.selectedFilters.join(',');

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.instantCartsUrl+'?search='+this.searchWord+'&status='+statuses)
                            .then(({data}) => {

                                //  Get the instant carts
                                self.instantCarts = data['_embedded']['instant_carts'] || [];

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

            //  Get the location instant carts
            this.fetchInstantCarts();

            //  If we want to add an instant cart
            if( this.$route.query.add_instant_cart == 'true' ){

                this.handleAddInstantCart();

            }



        }
    };

</script>
