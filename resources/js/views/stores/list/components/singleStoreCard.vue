<template>

    <Card @mouseover.native="isHovering = true"
          @mouseout.native="isHovering = false"
          @click.native="navigateToViewStore()"
          :class="['bos-mini-card', 'cursor-pointer', 'mb-3']" >

        <Row>

            <Col :span="24">

                <!-- Subscription Countdown timer -->
                <transition name="slide-right-fade">

                    <countdown v-if="hasSubscribed" :datetime="subscriptionExpiryTime"
                               :title="'Subscription #'+subscriptionId+':' " position="right"
                               :humanize="true" @expired="handleSubscriptionExpiryStatus()">
                    </countdown>

                    <small v-else :style="{ position: 'absolute', right: '0' }"
                            :class="['font-weight-bold', 'text-danger', 'border-bottom-dashed']">No subscription</small>

                </transition>

                <div class="d-flex pb-2">

                    <!-- Store Image -->
                    <div :class="['ivu-avatar', 'ivu-avatar-square', 'ivu-avatar-default']" :style="avatarStyles">
                        <span class="font-weight-bold">{{ store.name | firstLetter}}</span>
                    </div>

                    <!-- Store Name -->
                    <span class="cut-text font-weight-bold mt-2 ml-2">{{ store.name }}</span>

                </div>

                <div :class="contentClasses">

                    <template v-if="!isLoading">

                        <div v-if="!hasSubscribed">

                            <!-- Dial to pay -->
                            <dialToPay :resource="store" name="Store" description="Subscribe to get your store online"
                                        @updated="handlePaymentShortCodeUpdate()" @isLoading="isLoading">
                            </dialToPay>

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

                    </template>

                </div>

                <transition v-if="!isLoading" name="slide-right-fade">

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
    import deleteStoreModal from './deleteStoreModal.vue';
    import dialToPay from '../../../payment/dialToPay.vue';
    import miscMixin from './../../../../components/_mixins/misc/main.vue';
    import countdown from './../../../../components/_common/countdown/default.vue';

    export default {
        mixins: [miscMixin],
        components: { deleteStoreModal, dialToPay, countdown },
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
            hasSubscribed(){
                return this.store['_attributes']['subscription'] ? true : false;
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
                return this.store['_attributes']['payment_short_code'] ? true : false;
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
                return this.store.hex_color;
            },
            avatarStyles(){
                return {
                    border: '1px solid #' + this.hexColor + ' !important',
                    background: '#' + this.hexColor + '20 !important',
                    color: '#'+this.hexColor + ' !important',
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
            handleSubscriptionExpiryStatus(){

                //  Refetch the store
                this.getStore();

            },
            handlePaymentShortCodeUpdate(){

                //  Refetch the store
                this.getStore();

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
