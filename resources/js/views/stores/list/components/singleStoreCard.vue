<template>

    <Card @mouseover.native="isHovering = true"
          @mouseout.native="isHovering = false"
          @click.native="navigateToViewStore()"
          :class="['bos-mini-card', 'cursor-pointer', 'mb-3']" >

        <Row>

            <Col :span="24">

                <!-- Subscription Countdown timer -->
                <transition name="slide-right-fade">

                    <countdown v-if="subscriptionExpiryTime" :datetime="subscriptionExpiryTime"
                               :title="'Subscription #'+subscriptionId+':' " position="right" :humanize="true">
                    </countdown>

                </transition>

                <div class="d-flex pb-2">

                    <!-- Store Image -->
                    <Avatar shape="square" :style="avatarStyles">

                        <!-- Note: "firstLetter" filter is registered as a custom mixin -->
                        <span class="font-weight-bold">{{ store.name | firstLetter }}</span>

                    </Avatar>

                    <!-- Store Name -->
                    <span class="cut-text font-weight-bold mt-2 ml-2">{{ store.name }}</span>

                </div>

                <div :class="contentClasses">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isLoading" class="mt-2">Loading...</Loader>

                    <div v-if="!hasSubscribed">

                        <template v-if="hasPaymentShortCode">

                            <div :class="['bg-light', 'rounded-pill', 'px-3', 'py-1']">

                                <span>Dial to pay: </span>
                                <span :class="['text-primary', 'font-weight-bold']">{{ paymentShortCode.dialing_code }}</span>

                                <!-- Show the short code details -->
                                <Poptip trigger="hover" word-wrap width="300">

                                    <div slot="content" class="py-2" :style="{ lineHeight: 'normal' }">
                                        <p>Dial <span class="text-primary">{{ paymentShortCode.dialing_code }}</span> to pay for your store</p>
                                    </div>

                                    <!-- Show the info icon -->
                                    <Icon type="ios-information-circle-outline" :size="16" />

                                </Poptip>

                            </div>

                            <!-- Payment Short Code Countdown timer -->
                            <transition name="slide-right-fade">

                                <countdown v-if="paymentShortCodeExpiryTime" :datetime="paymentShortCodeExpiryTime" position="right" class="mr-2 mt-2"></countdown>

                            </transition>

                        </template>

                        <template v-else>

                            <!-- Pay Now Button -->
                            <Button v-if="!isLoading" type="success" class="float-right" @click.native.stop="generatePaymentShortcode()">Pay now</Button>

                        </template>

                    </div>

                    <template v-else>

                        <span class="d-inline-block mr-2">

                            <Badge :text="statusText" :status="status"></Badge>

                            <!-- If we are offline and have a reason provided -->
                            <Poptip v-if="!store.online && store.offline_message" trigger="hover" :content="store.offline_message" word-wrap width="300">
                                <!-- Show the info icon with the information of why we are offline -->
                                <Icon type="ios-information-circle-outline" :size="16" />
                            </Poptip>

                        </span>

                        <span class="d-inline-block">

                            <!-- Locations Button -->
                            <Button type="dashed" size="small" class="text-primary" @click.native.stop="navigateToViewStoreLocations()">
                                {{ numberOfLocations }} {{ numberOfLocations == 1 ? ' Location' : ' Locations' }}
                            </Button>

                        </span>

                    </template>

                </div>

                <transition name="slide-right-fade">

                    <div v-show="isHovering" class="clearfix">

                        <div class="float-right">

                            <!-- Delete store -->
                            <Button type="dashed" size="small" class="text-danger" :disabled="isLoading" @click.native.stop="handleOpenDeleteStoreModal()">Delete</Button>

                            <template v-if="hasSubscribed">

                                <!-- Clone store -->
                                <Button type="dashed" size="small" icon="ios-copy-outline" @click.native.stop="navigateToCloneStore()">Clone</Button>

                                <!-- View store -->
                                <Button type="dashed" size="small" class="text-primary" @click.native.stop="navigateToViewStore()">View</Button>

                            </template>

                        </div>

                    </div>

                </transition>

            </Col>

        </Row>

        <!--
            MODAL TO DELETE STORE
        -->
        <template v-if="isOpenDeleteStoreModal">

            <deleteStoreModal
                :index="index"
                :store="store"
                :stores="stores"
                @deleted="$emit('deleted')"
                @visibility="isOpenDeleteStoreModal = $event">
            </deleteStoreModal>

        </template>

    </Card>

</template>

<script>

    //  Get the custom mixin file
    var customMixin = require('./../../../../mixin.js').default;

    import Loader from './../../../../components/_common/loaders/default.vue';
    import countdown from './../../../../components/_common/countdown/default.vue';
    import deleteStoreModal from './deleteStoreModal.vue';

    export default {
        mixins: [customMixin],
        components: { Loader, countdown, deleteStoreModal },
        props: {
            store: {
                type: Object,
                default: null
            },
            stores: {
                type: Array,
                default:() => []
            }
        },
        data(){
            return {
                isLoading: false,
                isHovering: false,
                isOpenDeleteStoreModal: false,
            }
        },
        computed: {
            index(){
                return parseInt(Object.keys(this.stores).find(key => this.stores[key]['id'] === this.store['id']));
            },
            storeUrl(){
                return this.store['_links']['self'].href;
            },
            encodedStoreUrl(){
                return encodeURIComponent(this.storeUrl);
            },
            generatePaymentShortcodeUrl(){
                return this.store['_links']['bos:generate-payment-shortcode'].href;
            },
            hasSubscribed(){
                return this.store['_attributes']['has_subscribed'];
            },
            subscription(){
                return (this.store['_attributes']['subscription'] || {});
            },
            subscriptionId(){
                return this.subscription.id;
            },
            subscriptionExpiryTime(){
                return this.subscription.end_at;
            },
            hasPaymentShortCode(){
                return this.store['_attributes']['has_payment_short_code'];
            },
            paymentShortCode(){
                return (this.store['_attributes']['payment_short_code'] || {});
            },
            paymentShortCodeExpiryTime(){
                return this.paymentShortCode.expires_at;
            },

            numberOfLocations(){
                return this.store['_links']['bos:locations'].total;
            },
            contentClasses(){

                if( (!this.hasSubscribed && !this.hasPaymentShortCode) || this.isLoading){

                    return ['clearfix', 'mb-4'];

                }

                return ['sce-mini-card-body', 'py-2', 'pl-2', 'pr-5', 'mb-3'];
            },
            hexColor(){
                /** Note that vue does not allow us to use filters inside the component props
                 *  e.g :style="{ background: (myProperty | Filter) }" or directly inside
                 *  computed properties e.g return (myProperty | Filter). We can only use
                 *  filters inside interpolations e.g {{ myProperty | Filter }}, and you
                 *  can't use interpolations as attributes e.g
                 *
                 *  :style="{ background: {{ (myProperty | Filter) }} }"
                 *
                 *  To overcome this challenge we need to access the filter method directly
                 *  by accessing the current component Vue Instance then pass the data to
                 *  the filter method.
                 */
                return this.$options.filters.firstLetterColor(this.store.name);
            },
            avatarStyles(){
                return {
                    border: '1px solid ' + this.hexColor + ' !important',
                    background: this.hexColor + '20 !important',
                    color: this.hexColor + ' !important',
                }
            },
            statusText(){
                return this.store.online ? 'Online' : 'Offline'
            },
            status(){
                return this.store.online ? 'success' : 'error'
            }
        },
        methods: {
            navigateToViewStore(){

                if( this.storeUrl && this.hasSubscribed ){

                    //  Navigate to show the store
                    this.$router.push({ name: 'show-store-overview', params: { store_url: this.encodedStoreUrl } });

                }

            },
            navigateToCloneStore(){

                if( this.storeUrl ){

                    //  Navigate to clone store
                    this.$router.push({ name: 'create-store', query: { store_url: this.encodedStoreUrl } });

                }

            },
            navigateToViewStoreLocations(){

                if( this.storeUrl && this.hasSubscribed ){

                    //  Navigate to store locations
                    this.$router.push({ name: 'show-locations', params: { store_url: this.encodedStoreUrl } });

                }

            },
            generatePaymentShortcode(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                return api.call('post', this.generatePaymentShortcodeUrl)
                    .then(({data}) => {

                        //  Stop loader
                        self.isLoading = false;

                        //  Get the store (latest instance)
                        self.getStore();

                    }).catch((response) => {

                        console.log(response);

                        //  Stop loader
                        self.isLoading = false;

                });
            },
            getStore(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                return api.call('get', this.storeUrl)
                    .then(({data}) => {

                        //  Stop loader
                        self.isLoading = false;

                        //  Update the store
                        self.$set(self.stores, self.index, data);

                    }).catch((response) => {

                        console.log(response);

                        //  Stop loader
                        self.isLoading = false;

                });
            },
            handleOpenDeleteStoreModal(){
                this.isOpenDeleteStoreModal = true;
            }
        }
    }
</script>
