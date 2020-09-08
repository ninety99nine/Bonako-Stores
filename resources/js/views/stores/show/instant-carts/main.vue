<template>
    
    <Row :gutter="12" class="mt-5">

        <Col :span="20" :offset="2">

            <h1 class="border-bottom-dashed pb-3 mb-3">Instant Carts</h1>

            <div class="clearfix">

                <!-- Add Instant Cart Button -->
                <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true" iconDirection="left"
                            class="float-right mb-2" :ripple="!instantCartsExist" :disabled="isLoading"
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
                :store="store"
                :isCloning="false"
                :isEditing="false"
                :location="location"
                :instantCarts="instantCarts"
                @createdInstantCart="handleCreatedInstantCart($event)"
                @visibility="isOpenManageInstantCartDrawer = $event">
            </manageInstantCartDrawer>
    
        </template>

    </Row>

</template>

<script>

    import manageInstantCartDrawer from './manageInstantCartDrawer.vue';
    import Loader from './../../../../components/_common/loaders/default.vue';
    import basicButton from './../../../../components/_common/buttons/basicButton.vue';

    export default {
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
                columns: [
                    {
                        title: 'Name',
                        key: 'name'
                    },
                    {
                        title: 'Prodcuts',
                        key: 'products',
                        render: (h, params) => {
                            return h('div', this.renderProductTags(h, params))
                        }
                    },
                    {
                        title: 'Coupons',
                        key: 'coupons',
                        render: (h, params) => {
                            return h('div', this.renderCouponTags(h, params))
                        }
                    },
                    {
                        title: 'Code',
                        key: 'short_code'
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
            }          
        },
        methods: {
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

                        var quantity = product.pivot.quantity;
                        var name = product.name;

                        return h('ListItem', {
                            class: ['font-weight-bold']
                        },  quantity+'x('+name+')');
                    });
                    
                    content.push(

                        h('List', {
                            slot: 'content',
                            props: {
                                slot: 'content',
                                size: 'small'
                            }
                        }, productList)
                    );

                }

                //  Return the single product tag
                return [

                    h('Poptip', {
                        props: {
                            title: 'Cart Products',
                            content: poptipContent,
                            trigger: 'hover',
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
            handleCreateInstantCart(){
                this.isOpenManageInstantCartDrawer = true;
            },
            handleCreatedInstantCart(instantCart){

                //  Add the new created instantCart to the top of the list
                this.instantCarts.unshift(instantCart);

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