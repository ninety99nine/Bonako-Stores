<template>

    <div>

        <!-- If viewing single order -->
        <template v-if="order && isViewingOrder">

            <!-- Single Order -->
            <singleOrder :store="store" :location="location" :assignedLocations="assignedLocations"
                            :orders="orders" :index="index" :order="order" @close="handleCloseOrder()"
                            @verified="handleVerifiedOrder">
            </singleOrder>

        </template>

        <!-- If viewing a list of orders -->
        <Row v-else class="mt-4">

            <Col :span="22" :offset="1">

                <!-- If viewing orders -->
                <template>

                    <!-- Heading & Watch Video Button -->
                    <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

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

                        <template slot-scope="{ row, index }" slot="action">

                            <div>
                                <Dropdown trigger="click" placement="bottom-end">
                                    <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                                    <DropdownMenu slot="list">
                                        <DropdownItem name="View" @click.native="handleViewOrder(row, index)">View</DropdownItem>
                                        <DropdownItem v-if="row._embedded.fulfillment_status.name === 'Unfulfilled'" name="Fulfil" @click.native="handleFulfilOrder(row, index)">Fulfil</DropdownItem>
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

    import verifyOrderDeliveryModal from './../components/verifyOrderDeliveryModal.vue';
    import modalMixin from './../../../../../components/_mixins/modal/main.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import statusTag from './../components/statusTag.vue';
    import singleOrder from './../show/main.vue';

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
            assignedLocations: {
                type: Array,
                default: []
            },
        },
        components: { verifyOrderDeliveryModal, statusTag, singleOrder },
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
                    'Paid', 'Unpaid', 'Fulfilled', 'Unfulfilled', 'Cancelled'
                ],
                selectedFilters: ['Unfulfilled'],
                tableColumnsToShowByDefault: [
                    'Selector', 'Order #', 'Customer', 'Mobile', 'Items', 'Payment Status',
                    'Fulfillment Status', 'Created Date', 'Total'
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
                return (this.location || {})['_links']['bos:orders'].href;
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
                        sortable: true,
                        render: (h, params) => {

                            var order_number = (params.row.number || '...')

                            return h('span', {
                                class: [(this.checkIfCancelledOrder(params.row) ? 'text-danger' : '')],
                                on: {
                                    click: () => {
                                        this.activeOrderUrl = ((params.row._links || {}).self || {}).href;
                                    }
                                }
                            }, [
                                h('Button', {
                                    props: {
                                        type: 'text',
                                        ghost: true
                                    },
                                }, [
                                    h('span', {
                                        class: ['cut-text', (this.checkIfCancelledOrder(params.row) ? 'text-danger text-cancelled' : 'text-dark')]
                                    }, order_number)
                                ])
                            ]);
                        }
                    });
                }

                //  Customer Details
                if(this.tableColumnsToShowByDefault.includes('Customer')){
                    allowedColumns.push(
                    {
                        title: 'Customer',
                        render: (h, params) => {

                            var customer = (params.row._embedded.customer || {});
                            var first_name = (customer.first_name || '...');
                            var name = ((customer._attributes || {}).name || '...');

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
                                    title: 'Customer',
                                    content: name
                                }
                            }, [
                                h('span', {
                                    class: ['cut-text', 'text-capitalize', (this.checkIfCancelledOrder(params.row) ? 'text-danger text-cancelled' : '')]
                                }, first_name)
                            ])
                        }
                    });
                }

                //  Customer Mobile Number
                if(this.tableColumnsToShowByDefault.includes('Mobile')){
                    allowedColumns.push(
                    {
                        title: 'Mobile',
                        render: (h, params) => {

                            var customer = (params.row._embedded.customer || {});
                            var mobile_number = (customer.mobile_number || '...');

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
                                    title: 'Mobile',
                                    content: mobile_number
                                }
                            }, [
                                h('span', {
                                    class: ['cut-text', (this.checkIfCancelledOrder(params.row) ? 'text-danger text-cancelled' : '')]
                                }, mobile_number)
                            ])
                        }
                    })
                }

                //  Cart Items
                if(this.tableColumnsToShowByDefault.includes('Items')){
                    allowedColumns.push(
                    {
                        title: 'Items',
                        sortable: true,
                        align: 'center',
                        render: (h, params) => {

                            var activeCart = (params.row._embedded.active_cart || {});
                            var itemLines = ((activeCart._embedded || {}).item_lines || []);
                            var totalItems = (activeCart.total_items || 0);
                            var currency = (activeCart.currency || {});
                            var symbol = (currency.symbol || '');

                            var ListItems = itemLines.map((item) => {

                                var itemInfo = item.quantity+'x('+item.name+')'+ ' for '+
                                                this.formatPrice(item.sub_total, symbol);

                                var hasSaleDiscount = item.sale_discount_total ? true : false;
                                var saleDiscount = ' - '+this.formatPrice(item.sale_discount_total, symbol)+' sale discount';

                                return h('ListItem', {
                                        class: ['d-block']
                                    },[
                                        h('div', itemInfo),
                                        hasSaleDiscount ? h('div', {
                                            class: ['text-danger']
                                        }, saleDiscount) : null
                                    ]
                                );
                            });

                            return h('Poptip', {
                                style: {
                                    width: '100%'
                                },
                                props: {
                                    width: 350,
                                    wordWrap: true,
                                    trigger:'hover',
                                    placement: 'top',
                                    title: 'Cart Items'
                                },
                                class: ['breakdown-poptip']
                            }, [
                                h('span', {
                                    class: ['cut-text', (this.checkIfCancelledOrder(params.row) ? 'cancelled text-danger' : '')]
                                }, totalItems ),
                                h('List', {
                                        slot: 'content',
                                        props: {
                                            slot: 'content',
                                            size: 'small'
                                        }
                                    }, ListItems)
                            ])
                        }
                    })
                }

                //  Payment Status
                if(this.tableColumnsToShowByDefault.includes('Payment Status')){
                    allowedColumns.push(
                    {
                        title: 'Payment',
                        render: (h, params) => {
                            //  Payment Status Badge
                            return h(statusTag, {
                                props: {
                                    status: params.row._embedded.payment_status
                                }
                            })
                        }
                    })
                }

                //  Fulfillment Status
                if(this.tableColumnsToShowByDefault.includes('Fulfillment Status')){
                    allowedColumns.push(
                    {
                        title: 'Fulfillment',
                        render: (h, params) => {
                            //  Fulfillment Status Badge
                            return h(statusTag, {
                                props: {
                                    status: params.row._embedded.fulfillment_status
                                }
                            })
                        }
                    })
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
                                h('span', {
                                    class: ['cut-text', (this.checkIfCancelledOrder(params.row) ? 'text-danger text-cancelled' : '')]
                                }, this.formatDateTime(params.row.created_at.date))
                            ])
                        }
                    })
                }

                //  Grand Total
                if(this.tableColumnsToShowByDefault.includes('Total')){
                    allowedColumns.push(
                    {
                        title: 'Total',
                        sortable: true,
                        render: (h, params) => {

                            var activeCart = (params.row._embedded.active_cart || {});
                            var subTotal = (activeCart.sub_total || 0);
                            var couponTotal = (activeCart.coupon_total || 0);
                            var discountTotal = (activeCart.sale_discount_total || 0);
                            var deliveryFee = (activeCart.delivery_fee || 0);
                            var grandTotal = (activeCart.grand_total || 0);
                            var currency = (activeCart.currency || {});
                            var symbol = (currency.symbol || '');

                            return h('Poptip', {
                                style: {
                                    width: '100%',
                                    textAlign:'left'
                                },
                                props: {
                                    width: 280,
                                    wordWrap: true,
                                    trigger:'hover',
                                    placement: 'top-end',
                                    title: 'Breakdown'
                                },
                                class: ['breakdown-poptip']
                            }, [
                                h('span', {
                                    class: ['cut-text', (this.checkIfCancelledOrder(params.row) ? 'cancelled text-danger' : '')]
                                }, this.formatPrice(grandTotal, symbol) ),
                                h('List', {
                                        slot: 'content',
                                        props: {
                                            slot: 'content',
                                            size: 'small'
                                        }
                                    }, [
                                        h('ListItem', 'Sub Total: '+this.formatPrice(subTotal, symbol) ),
                                        h('ListItem', {
                                            class: ['border-0', 'text-danger']
                                        }, 'Sale Discount: '+this.formatPrice(discountTotal, symbol) ),
                                        h('ListItem', {
                                            class: ['text-danger']
                                        }, 'Coupon Discount: '+this.formatPrice(couponTotal, symbol) ),
                                        h('ListItem', {
                                            class: ['border-0', deliveryFee ? '' : 'd-none']
                                        },'Delivery Fee: '+this.formatPrice(deliveryFee, symbol) ),
                                        h('ListItem', {
                                            class: ['font-weight-bold', 'mt-2'],
                                            style: { outline: 'double' }
                                        },'Grand Total: '+this.formatPrice(grandTotal, symbol) )
                                    ])
                            ])
                        }
                    })
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
            },
            handleCloseOrder(){
                this.isViewingOrder = false;
            },
            handleFulfilOrder(order, index){
                this.order = order;
                this.index = index;
                this.handleOpenVerifyOrderDeliveryModal();
            },
            handleOpenVerifyOrderDeliveryModal(){
                this.isOpenVerifyOrderDeliveryModal = true;
            },
            handleVerifiedOrder(order){
                this.$set(this.orders, this.index, order);
                this.fetchOrders();
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

                                //  Console log the data returned
                                console.log(data);

                                //  Get the store
                                self.orders = (((data || {})._embedded || {}).orders || []);

                                //  Stop loader
                                self.isLoading = false;

                            })
                            .catch(response => {

                                //  Log the responce
                                console.error(response);

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
