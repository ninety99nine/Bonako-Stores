<template>

    <Row>

        <Col :span="24">

            <Row :gutter="12" class="mb-4">

                <Col :span="8">

                    <Input type="text" size="large" placeholder="Search order..." icon="ios-search-outline"></Input>

                </Col>

                <Col :span="8">

                    <Poptip trigger="hover" content="Add filters for specific orders" word-wrap class="poptip-w-100">
                        <Select v-model="selectedStatuses" size="large" multiple class="w-100"
                                placeholder="Add filters" @on-change="fetchOrders()">
                            <Option v-for="(status, index) in statuses" :value="status" :key="index">{{ status }}</Option>
                        </Select>
                    </Poptip>

                </Col>

                <Col :span="8" class="clearfix">

                    <!-- Refresh Button -->
                    <Button type="default" size="default" :class="['float-right', 'mt-2']"
                            @click.native="fetchOrders()">
                        <Icon type="ios-refresh" class="mr-1" :size="20" />
                        <span>Refresh</span>
                    </Button>

                </Col>

            </Row>

        </Col>

        <Col :span="24">

            <Table class="order-table" :columns="dynamicColumns" :data="orders" :loading="isLoading"
                    no-data-text="No orders found" :style="{ overflow: 'visible' }">

                <template slot-scope="{ row, index }" slot="action">

                    <div class="order-table-actions">
                        <Dropdown trigger="click" placement="bottom-end">
                            <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                            <DropdownMenu slot="list">
                                <DropdownItem name="View">View</DropdownItem>
                                <DropdownItem name="Fulfil" @click.native="handleOpenVerifyOrderDeliveryModal(row)">Fulfil</DropdownItem>
                                <DropdownItem name="Delete" class="text-danger">Delete</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </div>

                </template>

            </Table>

        </Col>

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

    </Row>

</template>

<script>

    import verifyOrderDeliveryModal from './verifyOrderDeliveryModal.vue';
    import statusTag from './../components/statusTag.vue';
    import moment from 'moment';

    export default {
        props: {
            location: {
                type: Object,
                default: null
            },
        },
        components: { verifyOrderDeliveryModal, statusTag },
        data () {
            return {
                orders: [],
                order: null,
                isLoading: false,
                statuses: [
                    'Paid', 'Unpaid', 'Fulfilled', 'UnFulfilled', 'Cancelled'
                ],
                selectedStatuses: [],
                tableColumnsToShowByDefault: [
                    'Selector', 'Order #', 'Customer', 'Mobile', 'Items', 'Payment Status',
                    'Fulfillment Status', 'Created Date', 'Total'
                ],
                isOpenVerifyOrderDeliveryModal: false
            }
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
                            return h('span', {
                                class: [(params.row.status.name == 'Cancelled' ? 'text-danger' : '')],
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
                                        class: ['cut-text', (params.row.status.name == 'Cancelled' ? 'text-danger text-cancelled' : 'text-dark')]
                                    }, (params.row.number) || '...')
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
                                    content: ((params.row.customer_info || {}).first_name  || '...')+' '
                                             +((params.row.customer_info || {}).last_name  || '')
                                }
                            }, [
                                h('span', {
                                    class: ['cut-text', 'text-capitalize', (params.row.status.name == 'Cancelled' ? 'text-danger text-cancelled' : '')]
                                }, ((params.row.customer_info || {}).first_name) || '...')
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
                                    content: ((params.row.customer_info || {}).mobile_number  || '...')
                                }
                            }, [
                                h('span', {
                                    class: ['cut-text', (params.row.status.name == 'Cancelled' ? 'text-danger text-cancelled' : '')]
                                }, ((params.row.customer_info || {}).mobile_number  || '...'))
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

                            var symbol = 'P';
                            var itemLines = (params.row.item_lines || []);
                            var totalItems = itemLines.map((item) => {
                                    return parseInt(item.quantity)
                                }).reduce(function(a, b){
                                    return a + b;
                                }, 0);

                            var ListItems = itemLines.map((item) => {

                                var itemInfo = item.quantity+'x('+item.name+')'+ ' for '+
                                                this.formatPrice(item.grand_total, symbol);

                                var hasSaleDiscount = item.sale_discount ? true : false;
                                var saleDiscount = ' - '+this.formatPrice(item.sale_discount, symbol)+' sale discount';

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
                                    class: ['cut-text', (params.row.status.name == 'Cancelled' ? 'cancelled text-danger' : '')]
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
                                    status: params.row.payment_status
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
                                    status: params.row.fulfillment_status
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
                                    class: ['cut-text', (params.row.status.name == 'Cancelled' ? 'text-danger text-cancelled' : '')]
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

                            var subTotal = (params.row.sub_total || 0);
                            var couponTotal = (params.row.coupon_total || 0);
                            var discountTotal = (params.row.discount_total || 0);
                            var grandTotal = (params.row.grand_total || 0);

                            var symbol = 'P';

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
                                    class: ['cut-text', (params.row.status.name == 'Cancelled' ? 'cancelled text-danger' : '')]
                                }, this.formatPrice(grandTotal, symbol) ),
                                h('List', {
                                        slot: 'content',
                                        props: {
                                            slot: 'content',
                                            size: 'small'
                                        }
                                    }, [
                                        h('ListItem', 'Sub Total: '+this.formatPrice(subTotal, symbol) ),
                                        h('ListItem', 'Sale Discount: '+this.formatPrice(discountTotal, symbol) ),
                                        h('ListItem', 'Coupon Discount: '+this.formatPrice(couponTotal, symbol) ),
                                        h('ListItem', {
                                            class: ['font-weight-bold']
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
            },
            moment: function () {
                return moment();
            }
        },
        methods: {
            handleOpenVerifyOrderDeliveryModal(order){
                this.order = order;
                this.isOpenVerifyOrderDeliveryModal = true;
            },
            handleVerifiedOrder(){

            },
            formatDateTime(date, withTime = false) {
                if( withTime ){
                    return moment(date).format('MMM DD YYYY @H:mmA');
                }else{
                    return moment(date).format('MMM DD YYYY');
                }
            },
            formatPrice(money, symbol) {
                let val = (money/1).toFixed(2).replace(',', '.');
                return (symbol ? symbol : '') + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            },
            fetchOrders() {

                //  If we have the location orders url
                if( this.locationOrdersUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.locationOrdersUrl)
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
            }
        },
        created(){

            //  Fetch the location orders
            this.fetchOrders();

        }
    }
</script>
