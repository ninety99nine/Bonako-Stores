<template>

    <div>

        <!-- If viewing single order -->
        <template v-if="order && isViewingOrder">

            <!-- Single Order -->
            <singleOrder :store="store" :location="location" :assignedLocations="assignedLocations"
                         :orders="orders" :order="order" :addPadding="addPadding"
                         @verified="handleVerifiedOrder"
                         @close="handleCloseOrder()">
            </singleOrder>

        </template>

        <!-- If viewing a list of orders -->
        <Row v-else class="mt-4">

            <Col :span="addPadding ? 22 : 24" :offset="addPadding ? 1 : 0">

                <!-- If viewing orders -->
                <template>

                    <!-- Heading & Watch Video Button -->
                    <Row v-if="showHeader" :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                        <Col :span="12">

                            <!-- Heading -->
                            <h1 :class="['font-weight-bold', 'text-muted']">
                                Orders
                            </h1>

                        </Col>

                        <Col :span="12">

                            <div class="clearfix">

                                <!-- Watch Video Button -->
                                <Button type="primary" size="default" @click.native="fetchStores()" :class="['float-right']">
                                    <Icon type="ios-play-outline" class="mr-1" :size="20" />
                                    <span>Watch Video</span>
                                </Button>

                            </div>

                        </Col>

                    </Row>

                    <!-- Search Bar, Filters & Refresh Button -->
                    <Row :gutter="12" class="mb-4">

                        <Col :span="8">

                            <Input v-model="searchWord" type="text" size="default" clearable placeholder="Search order..." icon="ios-search-outline"></Input>

                        </Col>

                        <Col :span="8">

                            <Poptip trigger="hover" content="Add filters for specific orders" word-wrap class="poptip-w-100">
                                <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                        prefix="ios-funnel-outline" clearable placeholder="Add filters" @on-select="fetchOrders()">

                                        <OptionGroup label="Type">
                                            <Option v-for="(type, index) in types" :value="type.name" :key="index" :label="type.name">
                                                <span :class="['font-weight-bold']">{{ type.name }}</span>
                                                <span style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">{{ type.desc }}</span>
                                            </Option>
                                        </OptionGroup>

                                        <OptionGroup label="Status">
                                            <Option v-for="(status, index) in statuses" :value="status" :key="index" :label="status">
                                                <span :class="['font-weight-bold']">{{ status }}</span>
                                            </Option>
                                        </OptionGroup>

                                </Select>
                            </Poptip>

                        </Col>

                        <Col :span="8" class="clearfix">

                            <!-- Refresh Button -->
                            <Button type="default" size="default" :class="['float-right']"
                                    @click.native="fetchOrders()">
                                <Icon type="ios-refresh" class="mr-1" :size="20" />
                                <span>Refresh</span>
                            </Button>

                        </Col>

                    </Row>

                    <!-- Order Table -->
                    <Table class="order-table" :columns="dynamicColumns" :data="orders" :loading="isLoading"
                            no-data-text="No orders found" :style="{ overflow: 'visible' }">

                        <!-- Number Poptip -->
                        <orderNumber slot-scope="{ row, index }" slot="number" :order="row"></orderNumber>

                        <!-- Customer Poptip -->
                        <orderCustomer slot-scope="{ row, index }" slot="customer" :order="row"></orderCustomer>

                        <!-- Contacts Poptip -->
                        <orderCustomerContacts slot-scope="{ row, index }" slot="contacts" :order="row"></orderCustomerContacts>

                        <!-- Cart Items Poptip -->
                        <cartItems slot-scope="{ row, index }" slot="items" :cart="(row._embedded || {}).active_cart" position="center"></cartItems>

                        <!-- Cart Coupons Poptip -->
                        <cartCoupons slot-scope="{ row, index }" slot="coupons" :cart="(row._embedded || {}).active_cart" position="center"></cartCoupons>

                        <!-- Payment Status -->
                        <orderStatusBadge slot-scope="{ row, index }" slot="payment" :status="(row._embedded || {}).payment_status"></orderStatusBadge>

                        <!-- Delivery Status -->
                        <orderStatusBadge slot-scope="{ row, index }" slot="delivery" :status="(row._embedded || {}).delivery_status"></orderStatusBadge>

                        <!-- Cart Total Poptip -->
                        <cartPricing slot-scope="{ row, index }" slot="total" :cart="(row._embedded || {}).active_cart"></cartPricing>

                        <!-- Created Date Poptip -->
                        <template slot-scope="{ row, index }" slot="date">

                            <Poptip trigger="hover" placement="top" width="300">

                                <span slot="content" :class="['text-center', 'd-block']">
                                    <span>Created Date</span>
                                    <span :class="['text-success', 'd-block']" :style="{ fontSize: '20px' }">{{ formatDateTime(row.created_at.date, true) }}</span>
                                </span>

                                <span>{{ formatDateTime(row.created_at.date) }}</span>

                            </Poptip>

                        </template>

                        <template slot-scope="{ row, index }" slot="action">

                            <div>
                                <Dropdown trigger="click" placement="bottom-end">
                                    <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                                    <DropdownMenu slot="list">
                                        <DropdownItem name="View" @click.native="handleViewOrder(row, index)">View</DropdownItem>
                                        <DropdownItem v-if="row._embedded.delivery_status.name === 'Undelivered'" name="Deliver" @click.native="handleDeliverOrder(row, index)">Deliver</DropdownItem>
                                        <DropdownItem name="Cancel" class="text-danger">Cancel</DropdownItem>
                                    </DropdownMenu>
                                </Dropdown>
                            </div>

                        </template>

                    </Table>

                </template>

                <!--
                    MODAL TO VERIFY ORDER DELIVERY
                -->
                <template v-if="isOpenVerifyOrderDeliveryModal">

                    <verifyOrderDeliveryModal
                        :order="order"
                        @verified="handleVerifiedOrder"
                        @visibility="isOpenVerifyOrderDeliveryModal = $event">
                    </verifyOrderDeliveryModal>

                </template>

            </Col>

        </Row>

    </div>

</template>

<script>

    import singleOrder from './../show/main.vue';
    import orderNumber from './../show/components/orderNumber.vue';
    import orderCustomer from './../show/components/orderCustomer.vue';
    import cartItems from './../../carts/show/components/cartItems.vue';
    import cartCoupons from './../../carts/show/components/cartCoupons.vue';
    import cartPricing from './../../carts/show/components/cartPricing.vue';
    import orderStatusBadge from './../show/components/orderStatusBadge.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import modalMixin from './../../../../../components/_mixins/modal/main.vue';
    import orderCustomerContacts from './../show/components/orderCustomerContacts.vue';
    import verifyOrderDeliveryModal from './../components/verifyOrderDeliveryModal.vue';

    export default {
        mixins: [ miscMixin, modalMixin ],
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            customer: {
                type: Object,
                default: null
            },
            assignedLocations: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            showHeader: {
                type: Boolean,
                default: true
            },
            addPadding: {
                type: Boolean,
                default: true
            }
        },
        components: {
            singleOrder, orderNumber, orderCustomer, cartItems, cartCoupons, cartPricing, orderStatusBadge,
            orderCustomerContacts, verifyOrderDeliveryModal
        },
        data () {
            return {
                orders: [],
                order: null,
                index: null,
                isLoading: false,
                isViewingOrder: false,
                types: [
                    {
                        name: 'Shared',
                        desc: 'Orders shared by locations'
                    },
                    {
                        name: 'Received',
                        desc: 'Orders sent by customers'
                    }
                ],
                statuses: [
                    'Paid', 'Unpaid', 'Pending', 'Delivered', 'Undelivered', 'Cancelled', 'Archieved'
                ],
                selectedFilters: ['Undelivered'],
                tableColumnsToShowByDefault: [
                    'Selector', 'Order #', 'Customer', 'Contacts', 'Items', 'Coupons', 'Payment Status',
                    'Delivery Status', 'Created Date', 'Total'
                ],
                isOpenVerifyOrderDeliveryModal: false,
                searchTimeout: null,
                searchWord: '',
            }
        },
        watch: {
            /**
             *  Search orders only 1 second after the user is done typing.
             */
            searchWord: function (val) {

                //  Clear the search timeout variable
                clearTimeout(this.searchTimeout);

                this.searchTimeout = setTimeout(() => {

                    //  Get the orders
                    this.fetchOrders();

                }, 1000); // 1 sec delay
            },
            //  Keep track of changes on the orders
            orders: {

                handler: function (val, oldVal) {

                    this.$emit('fetchLocationTotals');

                },
                deep: true

            },
        },
        computed: {
            locationOrdersUrl(){
                //  If we have a customer
                if( this.customer ){
                    return (this.customer || {})['_links']['bos:orders'].href;
                }else{
                    return (this.location || {})['_links']['bos:orders'].href;
                }
            },
            dynamicColumns(status){

                var allowedColumns = [];

                //  Order Selector
                if(this.tableColumnsToShowByDefault.includes('Selector')){
                    allowedColumns.push({
                        type: 'selection',
                        align: 'center',
                        width: 60
                    });
                }

                //  Order #
                if(this.tableColumnsToShowByDefault.includes('Order #')){
                    allowedColumns.push(
                        {
                            title: 'Order #',
                            slot: 'number'
                        }
                    );
                }

                //  Order Customer
                if(this.tableColumnsToShowByDefault.includes('Customer')){
                    allowedColumns.push(
                        {
                            title: 'Customer',
                            slot: 'customer',
                            width: 140
                        }
                    );
                }

                //  Order Customer Contacts
                if(this.tableColumnsToShowByDefault.includes('Contacts')){
                    allowedColumns.push(
                        {
                            title: 'Contacts',
                            slot: 'contacts',
                            width: 140
                        }
                    );
                }

                //  Cart Items
                if(this.tableColumnsToShowByDefault.includes('Items')){
                    allowedColumns.push(
                        {
                            title: 'Items',
                            slot: 'items',
                            align: 'center'
                        }
                    );
                }

                //  Cart Coupons
                if(this.tableColumnsToShowByDefault.includes('Coupons')){
                    allowedColumns.push(
                        {
                            title: 'Coupons',
                            slot: 'coupons',
                            align: 'center'
                        }
                    );
                }

                //  Payment Status
                if(this.tableColumnsToShowByDefault.includes('Payment Status')){
                    allowedColumns.push(
                        {
                            title: 'Payment',
                            slot: 'payment'
                        }
                    );
                }

                //  Delivery Status
                if(this.tableColumnsToShowByDefault.includes('Delivery Status')){
                    allowedColumns.push(
                        {
                            title: 'Delivery',
                            slot: 'delivery'
                        }
                    );
                }

                //  Created Date
                if(this.tableColumnsToShowByDefault.includes('Created Date')){
                    allowedColumns.push(
                    {
                        title: 'Date',
                        slot: 'date',
                        width: 120,
                    })
                }

                //  Order Grand Total
                if(this.tableColumnsToShowByDefault.includes('Total')){
                    allowedColumns.push(
                        {
                            title: 'Total',
                            slot: 'total'
                        }
                    );
                }

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
            checkIfCancelledOrder(order){
                return order['_embedded']['status']['name'] == 'Cancelled' ? true : false;
            },
            handleViewOrder(order, index){
                this.order = order;
                this.index = index;
                this.isViewingOrder = true;

                //  Notify parent
                this.$emit('viewingOrder', true);
            },
            handleCloseOrder(){
                this.isViewingOrder = false;
                this.fetchOrders();

                //  Notify parent
                this.$emit('viewingOrder', false);
            },
            handleDeliverOrder(order, index){
                this.order = order;
                this.index = index;
                this.handleOpenVerifyOrderDeliveryModal();
            },
            handleOpenVerifyOrderDeliveryModal(){
                this.isOpenVerifyOrderDeliveryModal = true;
            },
            handleVerifiedOrder(order){
                this.$set(this.orders, this.index, order);
            },
            fetchOrders() {

                /**
                 *  Note that we need to use the $nextTick() method to get the latest data of the
                 *  "selectedFilters". This is because everytime we trigger the select option
                 *  "on-select" event, it always brings the "selectedFilters" before its
                 *  updated with the latest selected/unselected option data. This is not
                 *  desired, so the $nextTick() method helps us get the latest updates.
                 */
                this.$nextTick(() => {

                    //  If we have the location orders url
                    if( this.locationOrdersUrl ){

                        //  Hold constant reference to the current Vue instance
                        const self = this;

                        //  Start loader
                        self.isLoading = true;

                        var statuses = this.selectedFilters.filter((value) => {

                            //  If its in the list of statuses then return
                            return this.statuses.includes(value);

                        }).join(',');

                        var types = this.selectedFilters.filter((value) => {

                            //  If its in the list of types then return
                            return this.types.map((type) => { return type.name }).includes(value);

                        }).join(',');

                        //  Use the api call() function, refer to api.js
                        api.call('get', this.locationOrdersUrl+'?search='+this.searchWord+'&status='+statuses+'&type='+types)
                            .then(({data}) => {

                                //  Get the store
                                self.orders = (((data || {})._embedded || {}).orders || []);

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

            //  Fetch the location orders
            this.fetchOrders();

        }
    }
</script>
