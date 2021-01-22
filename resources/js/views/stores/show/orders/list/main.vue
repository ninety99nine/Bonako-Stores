<template>

    <Row>

        <Col :span="24">

            <Row class="mb-4">

                <Col :span="16">

                    <Input type="text" size="large" placeholder="Search order..." icon="ios-search-outline"></Input>

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
            </Table>

        </Col>

    </Row>

</template>

<script>

    import statusTag from './../components/statusTag.vue';
    import moment from 'moment';

    export default {
        props: {
            location: {
                type: Object,
                default: null
            },
        },
        components: { statusTag },
        data () {
            return {
                orders: [],
                isLoading: false,
                tableColumnsToShowByDefault: [
                    'Selector', 'Order #', 'Customer', 'Mobile', 'Items', 'Payment Status',
                    'Fulfillment Status', 'Created Date', 'Total'
                ]
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
                                    content: 'Customer: '+((params.row.customer_info || {}).first_name  || '...')+' '
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
                                    content: 'Mobile: '+((params.row.customer_info || {}).mobile_number  || '...')
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
                                    width: 280,
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
                                    placement: 'top-start',
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
                        key: 'action',
                        width: 150,
                        render: (h, params) => {
                            return h('div', {
                                    class: ['order-table-actions']
                                }, [
                                h('Button', {
                                    props: {
                                        type: 'primary',
                                        size: 'small',
                                        ghost: true
                                    },
                                    style: {
                                        marginRight: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            this.show(params.index)
                                        }
                                    }
                                }, 'View'),
                                (params.row.status.name == 'Cancelled' ? null : h('Button', {
                                    props: {
                                        type: 'error',
                                        size: 'small',
                                        ghost: true
                                    },
                                    on: {
                                        click: () => {
                                            this.remove(params.index)
                                        }
                                    }
                                }, 'Cancel'))
                            ]);
                        }
                    }
                );

                return allowedColumns;
            },
            moment: function () {
                return moment();
            }
        },
        methods: {
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
