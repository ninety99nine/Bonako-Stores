<template>

    <Row class="mt-4">

        <Col :span="22" :offset="1">

            <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                <Col :span="12">

                    <!-- Heading -->
                    <h1 :class="['font-weight-bold', 'text-muted']">
                        Overview
                    </h1>

                </Col>

                <Col :span="12">

                    <div class="clearfix">

                        <!-- Refresh Button -->
                        <Button type="default" size="default" :class="['float-right']"
                                @click.native="refetchLocation()">
                            <Icon type="ios-refresh" class="mr-1" :size="20" />
                            <span>Refresh</span>
                        </Button>

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" :class="['float-right', 'mr-2']"
                                @click.native="fetchStores()">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

            </Row>

            <Row :gutter="12">

                <Col :span="8">

                    <Card :style="generalCardStyles">

                        <!-- Store name -->
                        <h1 :style="{ fontSize: '16px' }" class="mb-2" >
                            <span>Welcome to</span>
                            <span :class="['font-weight-bold', 'text-primary']" :style="{ fontSize: '1.5em' }">
                                {{ store.name }}
                            </span>
                        </h1>

                        <!-- Store short code -->
                        <h2 :style="{ fontSize: '16px' }">
                            <span>Dial</span>
                            <span :class="['font-weight-bold', 'text-primary', 'border-bottom-dashed']"
                                :style="{ fontSize: '1.5em' }">
                                {{ visitShortCodeDialingCode }}
                            </span>
                            <span>to visit store</span>
                        </h2>

                        <!-- Subscription Countdown -->
                        <countdown class="mt-3" :datetime="myActiveSubscriptionExpiryTime"
                                    :title="'Subscription #'+myActiveSubscriptionId+':' "
                                    position="left" :humanize="true">
                        </countdown>

                    </Card>

                </Col>

                <Col :span="8">

                    <Card :class="clickableCardClasses" :style="generalCardStyles" @click.native="navigateToMenuLink('show-store-orders')">

                        <h1 :style="{ fontSize: '20px' }" :class="['text-center', 'mb-2']">
                            <span>Orders</span>
                        </h1>

                        <h2 :style="{ fontSize: '20px' }" :class="['text-center']">
                            <span :class="['font-weight-bold', 'text-center', 'border-bottom-dashed']"
                                :style="{ fontSize: '1.5em' }">
                                {{ locationUndeliveredOrders }}
                            </span>
                        </h2>

                    </Card>

                </Col>

                <Col :span="8">

                    <Card :class="clickableCardClasses" :style="generalCardStyles" @click.native="navigateToMenuLink('show-store-products')">

                        <h1 :style="{ fontSize: '20px' }" :class="['text-center', 'mb-2']">
                            <span>Products</span>
                        </h1>

                        <h2 :style="{ fontSize: '20px' }" :class="['text-center']">
                            <span :class="['font-weight-bold', 'text-center', 'border-bottom-dashed']"
                                :style="{ fontSize: '1.5em' }">
                                {{ locationProductsTotal }}
                            </span>
                        </h2>

                    </Card>

                </Col>

                <Col v-for="(actionCard, index) in actionCards" :key="index" :span="24" class="mt-4">

                    <Card :style="getActionCardStyles(actionCard.resource)">

                        <Row :gutter="12" :class="['position-relative']">

                            <Col :span="16">

                                <span :style="{ fontSize: '20px' }" :class="['font-weight-bold', 'border-bottom-dashed', 'p-2']">
                                    {{ actionCard.heading }}
                                </span>

                                <p :class="['mt-4']" :style="{ textAlign: 'justify' }">{{ actionCard.body }}</p>

                            </Col>

                            <Col :span="8" :class="['position-static']">

                                <Button type="success" :class="['position-absolute']"
                                        :style="{ bottom: '0', right: '0' }"
                                        @click.native="addResource(actionCard.resource)">
                                    <Icon type="md-add" :size="20" />
                                    <span>{{ actionCard.action }}</span>
                                </Button>

                            </Col>

                        </Row>

                    </Card>

                </Col>

            </Row>

        </Col>

    </Row>

</template>

<script>

    import basicButton from './../../../../components/_common/buttons/basicButton.vue';
    import countdown from './../../../../components/_common/countdown/default.vue';
    import Loader from './../../../../components/_common/loaders/default.vue';

    export default {
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            locationTotals: {
                type: Object,
                default: null
            },
        },
        components: { basicButton, countdown, Loader },
        data () {
            return {
                isLoading: false,
                actionCards: [
                    {
                        heading: 'Add Products to start selling',
                        body: 'Start adding products to sell on your store. You can sell food, beverages, vegetables, meat, farming products, building materials, make-up & cosmetics, clothes, tickets and more.',
                        action: 'Add Product',
                        resource: 'products'
                    },
                    {
                        heading: 'Add Coupons for discounts',
                        body: 'Start adding coupons to allow your customers to claim discounts or free delivery. Offer coupons that activate on special conditions e.g Offer a 10% discount only if the order has more than 2 items. Coupons can also be limited e.g Only valid for the first 500 customers.',
                        action: 'Add Coupon',
                        resource: 'coupons'
                    },
                    {
                        heading: 'Add Instant Carts for faster shopping',
                        body: 'Instant carts allow you to create baskets that already contain specific products inside. This allows you to take 2 or more products and make combos. Coupons can also be applied to offer special discounts or free delivery. Every instant cart comes with a shortcode that allows the customer to dial and checkout instantly.',
                        action: 'Add Instant Cart',
                        resource: 'instant carts'
                    }
                ]
            }
        },
        computed: {
            generalCardStyles(){
                return { minHeight: '140px' };
            },
            clickableCardClasses(){
                return ['cursor-pointer', 'py-3'];
            },
            locationProductsTotal(){
                if( this.locationTotals ){
                    return this.locationTotals.products.total;
                }
            },
            locationUndeliveredOrders(){
                if( this.locationTotals ){
                    return this.locationTotals.orders.received.statuses.undelivered;
                }
            },
            visitShortCode(){
                return (this.store['_attributes']['visit_short_code'] || {});
            },
            visitShortCodeDialingCode(){
                return this.visitShortCode.dialing_code;
            },
            visitShortCodeExpiryTime(){
                return this.visitShortCode.expires_at;
            },
            myActiveSubscription(){
                return (this.store['_attributes']['subscription'] || {});
            },
            myActiveSubscriptionId(){
                return (this.myActiveSubscription || {}).id;
            },
            myActiveSubscriptionExpiryTime(){
                return (this.myActiveSubscription || {}).end_at;
            },
            storeUrl(){
                return this.store['_links']['self'].href;
            }
        },
        methods: {
            getActionCardStyles(resource){
                var styles = {
                    minHeight: '140px',
                    borderTop: '5px solid #19be6b'
                }

                if( resource == 'products' ){

                    styles['borderTop'] = '5px solid #19be6b';

                }else if( resource == 'coupons' ){

                    styles['borderTop'] = '5px solid lime';

                }else if( resource == 'instant carts' ){

                    styles['borderTop'] = '5px solid greenyellow';

                }

                return styles;
            },
            navigateToMenuLink(linkName){
                this.$emit('navigateToMenuLink', linkName);
            },
            refetchLocation(){
                this.$emit('refetchLocation');
            },
            addResource(resource){

                if( resource == 'products' ){
                    this.addProduct();
                }else if( resource == 'coupons' ){
                    this.addCoupon();
                }else if( resource == 'instant carts' ){
                    this.addInstantCart();
                }

            },
            addProduct(){
                var route = {
                        name: 'show-store-products',
                        params: {
                            store_url: encodeURIComponent(this.storeUrl)
                        },
                        query: { add_product: true }
                    };

                this.$emit('navigateToRoute', route);
            },
            addCoupon(){
                var route = {
                        name: 'show-store-coupons',
                        params: {
                            store_url: encodeURIComponent(this.storeUrl)
                        },
                        query: { add_coupon: true }
                    };

                this.$emit('navigateToRoute', route);
            },
            addInstantCart(){
                var route = {
                        name: 'show-store-instant-carts',
                        params: {
                            store_url: encodeURIComponent(this.storeUrl)
                        },
                        query: { add_instant_cart: true }
                    };

                this.$emit('navigateToRoute', route);
            }
        }
    }
</script>
