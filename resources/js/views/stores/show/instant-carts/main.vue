<template>
    
    <Row :gutter="12" class="mt-5">

        <Col :span="20" :offset="2">

            <h1 class="border-bottom-dashed pb-3 mb-3">Instant Carts</h1>
            
            <!-- We can only add an instant cart if we have a location selected -->
            <div class="clearfix">

                <!-- Add Instant Cart Button -->
                <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true" iconDirection="left"
                            class="float-right mb-2" :ripple="!instantCartsExist && !isLoading" :disabled="isLoading"
                            @click.native="handleCreateInstantCart()">
                    <span>Add Instant Cart</span>
                </basicButton>

            </div>
            
            <Card class="pt-2">

                <Table :columns="columns" :data="instantCarts" :loading="isLoading" :style="{ overflow: 'initial' }"></Table>

                <div class="clearfix">

                    <!-- Refresh Button -->
                    <Button type="default" size="small" class="float-right mt-2" @click.native="fetchInstantCarts()">
                        <span class="d-flex">
                            <Icon type="ios-refresh" :size="20" class="mr-1"/>
                            <span>Refresh</span>
                        </span>
                    </Button>

                </div>

            </Card>

        </Col>

        <!-- 
            MODAL TO ADD / CLONE / EDIT INSTANT CART
        -->
        <template v-if="isOpenManageInstantCartDrawer">

            <manageInstantCartDrawer
                :index="index"
                :store="store"
                :location="location"
                :instantCart="instantCart"
                :instantCarts="instantCarts"
                @isSaving="isLoading = $event"
                @isCreating="isLoading = $event"
                @savedInstantCart="handleSavedInstantCart"
                @createdInstantCart="handleCreatedInstantCart($event)"
                @visibility="isOpenManageInstantCartDrawer = $event">
            </manageInstantCartDrawer>
    
        </template>

    </Row>

</template>

<script>

    import manageInstantCartDrawer from './manageInstantCartDrawer.vue';
    import miscMixin from './../../../../components/_mixins/misc/main.vue';
    import Loader from './../../../../components/_common/loaders/default.vue';
    import basicButton from './../../../../components/_common/buttons/basicButton.vue';

    export default {
        mixins: [miscMixin],
        components: { 
            manageInstantCartDrawer, Loader, basicButton 
        },
        props: { 
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            }
        },
        data(){
            return {
                instantCarts: [],
                isLoading: false,
                instantCart: null,
                columns: [
                    {
                        title: 'Name',
                        key: 'name',
                        width: 200
                    },
                    {
                        title: 'Location',
                        render: (h, params) => {
                            return h('div', this.renderLocationTag(h, params))
                        }
                    },
                    {
                        title: 'Products',
                        render: (h, params) => {
                            return h('div', this.renderProductTags(h, params))
                        }
                    },
                    {
                        title: 'Coupons',
                        render: (h, params) => {
                            return h('div', this.renderCouponTags(h, params))
                        }
                    },
                    {
                        title: 'Total',
                        render: (h, params) => {
                            return h('div', this.renderTotal(h, params))
                        }
                    },
                    {
                        title: 'Code',
                        key: 'short_code'
                    },
                    {
                        title: '',
                        render: (h, params) => {
                            return h('Button', {
                                props: {
                                    type: 'primary',
                                    size: 'small'
                                },
                                style: {
                                    
                                },
                                on: {
                                    click: () => {
                                        this.handleEditInstantCart(params.row, params.index)
                                    }
                                }
                            }, 'Edit')
                        }
                    }
                ],
                isOpenManageInstantCartDrawer: false,
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
            storeUrl(){
                return this.store['_links']['self'].href;
            },
            encodedStoreUrl(){
                return encodeURIComponent(this.storeUrl);
            },
        },
        methods: {
            renderLocationTag(h, params){

                //  Get the product
                var location = params.row['_embedded'].location;

                return [
                    h('Tag', {
                        props: {
                            type: 'border',
                            color: 'blue'
                        }
                    }, location.name)
                ];

            },
            renderProductTags(h, params){

                //  Get the products
                var products = params.row['_embedded'].products;
                var showProduts = true;

                //  If we only have one product
                if( products.length >= 1 ){
                    
                    //  Set properties
                    var tagName = products.length + (products.length == 1 ? ' product' : ' products');
                    var poptipContent = null;
                    var tagColor = 'blue';

                }else{

                    //  Set properties
                    var poptipContent = 'No Products have been added to this instant cart';
                    var tagName = 'No Products';
                    var tagColor = 'warning';
                    var showProduts = false;

                }

                var content = [

                    h('Tag', {
                        props: {
                            type: 'border',
                            color: tagColor
                        }
                    }, tagName)

                ];

                if( showProduts ){

                    var productList = products.map((product) => {

                        var unit_regular_price = product.unit_regular_price;
                        var unit_sale_discount = product.unit_sale_discount;
                        var quantity = product.pivot.quantity;
                        var on_sale = product.on_sale;
                        var name = product.name;

                        var product_details = quantity+'x('+name+')';
                        var product_pricing = this.formatPrice((unit_regular_price * quantity), 'P');
                        var unit_sale_discount = (unit_sale_discount ? ' - '+this.formatPrice((unit_sale_discount * quantity), 'P') : '');
                        var on_sale = (on_sale ? ' (sale)' : '');

                        return h('tr', [
                            h('td', [ h('span', product_details) ]),
                            h('td', [ 
                                h('span', product_pricing),
                                h('small', { class: ['text-danger'] }, unit_sale_discount),
                                h('span', on_sale)
                            ])
                        ]);

                    });
                    
                    content.push(
                        h('table', {
                            slot: 'content',
                            class: ['table']
                        }, [
                            h('tbody', productList)
                        ])
                    );

                }

                //  Return the single product tag
                return [

                    h('Poptip', {
                        props: {
                            title: 'Cart Products',
                            content: poptipContent,
                            trigger: 'hover',
                            width: 500
                        }
                    }, content)

                ];

            },
            renderCouponTags(h, params){

                //  Get the coupons
                var coupons = params.row['_embedded'].coupons;
                var showProduts = true;

                //  If we only have one coupon
                if( coupons.length >= 1 ){
                    
                    //  Set properties
                    var tagName = coupons.length + (coupons.length == 1 ? ' coupon' : ' coupons');
                    var tagColor = 'green';
                    
                    var content = [

                        h('Tag', {
                            props: {
                                type: 'border',
                                color: tagColor
                            }
                        }, tagName)

                    ];

                    if( showProduts ){

                        var couponList = coupons.map((coupon) => {
                            return h('ListItem', {
                                class: ['font-weight-bold']
                            }, coupon.name);
                        });
                        
                        content.push(

                            h('List', {
                                slot: 'content',
                                props: {
                                    slot: 'content',
                                    size: 'small'
                                }
                            }, couponList)
                        );

                    }

                    //  Return the single coupon tag
                    return [

                        h('Poptip', {
                            props: {
                                title: 'Cart Coupons',
                                trigger: 'hover',
                            }
                        }, content)

                    ];

                }
            },
            renderTotal(h, params){

                //  Return the total
                return [
                    h('div', {
                            class: ['d-flex']
                        },
                        [
                            h('div', { class: ['mr-1'] },  this.formatPrice(params.row.cart.grand_total, 'P')),
                            h('Poptip', {
                                props: {
                                    title: 'Cart Pricing',
                                    trigger: 'hover',
                                    width: 300
                                }
                            }, [
                                h('table', {
                                    slot: 'content',
                                    class: ['table']
                                }, [
                                    h('tbody', [
                                        h('tr', [
                                            h('td', [ h('span', 'Sub Total') ]),
                                            h('td', [ h('span', this.formatPrice(params.row.cart.sub_total, 'P')) ])
                                        ]),
                                        h('tr', [
                                            h('td', [ h('span', 'Sale Discount') ]),
                                            h('td', [ h('span', this.formatPrice(params.row.cart.sale_discount_total, 'P')) ])
                                        ]),
                                        h('tr', [
                                            h('td', [ h('span', 'Coupon Discount') ]),
                                            h('td', [ h('span', this.formatPrice(params.row.cart.coupon_total, 'P')) ])
                                        ]),
                                        h('tr', [
                                            h('td', [ h('span', 'Grand Total') ]),
                                            h('td', [ h('span', { 
                                                class: ['font-weight-bold', 'border-bottom-dashed', 'border-dark'] 
                                            }, this.formatPrice(params.row.cart.grand_total, 'P')) ])
                                        ])
                                    ])
                                ]),
                                h('Icon', {
                                    props: {
                                        size: 16,
                                        type: 'ios-information-circle-outline'
                                    }
                                })

                            ])
                        ]),
                ];
                
            },
            handleCreateInstantCart(){
                this.index = null;
                this.instantCart = null;
                this.isOpenManageInstantCartDrawer = true;
            },
            handleCreatedInstantCart(instantCart){
                //  Add the new created instant cart to the top of the list
                this.instantCarts.unshift(instantCart);

            },
            handleEditInstantCart(instantCart, index){
                this.index = index;
                this.instantCart = instantCart;
                this.isOpenManageInstantCartDrawer = true;
            },
            handleSavedInstantCart(instantCart, index){

                //  Update the instantCarts
                this.$set(this.instantCarts, index, instantCart);

            },
            fetchInstantCarts() {

                //  If we have the location
                if( this.location ){

                    //  Get the location instant carts
                    var instantCartUrl = this.location['_links']['bos:instant_carts'].href;

                }else{

                    //  Get the store instant carts
                    var instantCartUrl = this.store['_links']['bos:instant_carts'].href;

                }

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                //  Use the api call() function, refer to api.js
                api.call('get', instantCartUrl)
                    .then(({data}) => {
                        
                        //  Console log the data returned
                        console.log(data);

                        //  Get the instant carts
                        self.instantCarts = data['_embedded']['instant_carts'] || [];

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
        },
        created(){

            //  Fetch the instant carts
            this.fetchInstantCarts();

        }
    };
  
</script>