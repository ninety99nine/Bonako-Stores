<template>

    <Row class="mt-3 pt-2">

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

                    <Card :class="clickableCardClasses" :style="generalCardStyles">

                        <h1 :style="{ fontSize: '20px' }" :class="['text-center', 'mb-2']">
                            <span>Orders</span>
                        </h1>

                        <h2 :style="{ fontSize: '20px' }" :class="['text-center']">
                            <span :class="['font-weight-bold', 'text-center', 'border-bottom-dashed']"
                                :style="{ fontSize: '1.5em' }">
                                {{ locationUnfulfilledOrdersTotal }}
                            </span>
                        </h2>

                    </Card>

                </Col>

                <Col :span="8">

                    <Card :class="clickableCardClasses" :style="generalCardStyles">

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

                <Col :span="24" class="mt-4">

                    <Card :style="addProductCardStyles">

                        <span :style="{ fontSize: '20px' }" :class="['font-weight-bold', 'border-bottom-dashed', 'p-2']">
                            Add Products to start selling
                        </span>

                        <p :class="['my-4']" :style="{ maxWidth: '700px' }">
                            Start adding products to sell on your store. You can sell food, beverages, vegetables, meat, farming products, building materials, make-up & cosmetics, clothes, tickets,
                        <div :class="['clearfix']">

                            <Button type="success" size="large" @click.native="addProduct()">
                                <Icon type="md-add" :size="20" />
                                <span>Add Product</span>
                            </Button>

                        </div>

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
        },
        components: { basicButton, countdown, Loader },
        data () {
            return {
                isLoading: false
            }
        },
        computed: {
            generalCardStyles(){
                return { minHeight: '140px' };
            },
            addProductCardStyles(){
                return {
                    minHeight: '140px',
                    borderTop: '5px solid #19be6b'
                }
            },
            clickableCardClasses(){
                return ['cursor-pointer', 'py-3'];
            },
            locationProductsTotal(){
                return this.location['_links']['bos:products'].total;
            },
            locationUnfulfilledOrdersTotal(){
                return (this.location || {})['_links']['bos:unfulfilled-orders'].total;
            },
            visitShortCodes(){
                return (((this.store['_embedded']['visit_short_codes'] || [])['_embedded'] || [])['short_codes'] || []);
            },
            visitShortCode(){
                if( this.visitShortCodes.length ){
                    return this.visitShortCodes[0];
                }
            },
            visitShortCodeDialingCode(){
                return (this.visitShortCode || {}).dialing_code;
            },
            visitShortCodeExpiryTime(){
                return (this.visitShortCode || {}).expires_at;
            },
            myActiveSubscriptions(){
                return (((this.store['_embedded']['my_active_subscriptions'] || [])['_embedded'] || [])['subscriptions'] || []);
            },
            myActiveSubscription(){
                if( this.myActiveSubscriptions.length ){
                    return this.myActiveSubscriptions[0];
                }
            },
            myActiveSubscriptionId(){
                return (this.myActiveSubscription || {}).id;
            },
            myActiveSubscriptionExpiryTime(){
                return (this.myActiveSubscription || {}).end_at;
            },
        },
        methods: {
            refetchLocation(){
                this.$emit('refetchLocation');
            },
            addProduct(){
                this.$emit('refetchLocation');
            }
        }
    }
</script>
