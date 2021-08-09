<template>

    <Row class="mt-4">

        <Col :span="22" :offset="1">

            <Row :gutter="12">

                <Col :span="24" :class="isViewingOrder ? ['mt-3'] : ['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isLoadingCustomer" class="mb-2">Searching customer</Loader>

                    <!-- Customer Name & Watch Video Button -->
                    <Row v-else-if="!isLoadingCustomer && localCustomer" :gutter="12">

                        <Col :span="24">

                            <div class="d-flex" :style="{ alignItems: 'flex-end' }">

                                <!-- Customers Button -->
                                <Button v-if="!isViewingOrder" type="default" size="default" class="mr-4" @click.native="closeCustomer()">
                                    <Icon type="md-arrow-back" class="mr-1" :size="20" />
                                    <span>Customers</span>
                                </Button>

                                <span :style="{ fontSize: 'x-large', lineHeight: 'initial' }"
                                      :class="isViewingOrder ? [] : ['font-weight-bold', 'mr-4']">
                                    {{ customerFullName }}
                                </span>

                                <span v-if="!isViewingOrder" :class="['text-muted']">{{ createdDateTime }}</span>

                            </div>

                        </Col>

                    </Row>

                    <!-- If we are not loading and don't have the customer -->
                    <Alert v-else-if="!isLoadingCustomer && !localCustomer" type="warning" class="mx-5" show-icon>
                        Customer Not Found
                        <template slot="desc">
                        We could not get the customer, try refreshing your browser. It's also possible that this customer has been deleted.
                        </template>
                    </Alert>

                </Col>

                <!-- If we are not loading and have the customer -->
                <template v-if="!isLoadingCustomer && localCustomer">

                    <Col :span="24">

                        <customerOrders :customer="localCustomer" :location="location" :showHeader="false" :addPadding="false" @viewingOrder="isViewingOrder = $event"></customerOrders>

                    </Col>

                </template>

            </Row>

        </Col>

    </Row>

</template>
<script>

    import localMixin from './mixin/main.vue';
    import customerOrders from './../../orders/list/main.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';

    export default {
        mixins: [localMixin, miscMixin],
        components: { Loader, customerOrders },
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
                default: function(){
                    return [];
                }
            },
            customer: {
                type: Object,
                default: null
            },
            customers: {
                type: Array,
                default: function(){
                    return [];
                }
            }
        },
        data () {
            return {
                isViewingOrder: false,
                isLoadingCustomer: false,
                localCustomer: this.customer,
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  Fetch the customer
                this.fetchCustomer();

            }
        },
        computed: {
            user(){
                return ((this.localCustomer._embedded || {}).user || {});
            },
            customerFullName(){
                return ((this.user || {})._attributes || {}).name;
            },
            createdDateTime(){
                return this.formatDateTime(this.localCustomer.created_at, 'MMM DD, YYYY')
                        +' at '+ this.formatDateTime(this.localCustomer.created_at, 'h:mma');
            },
            customerUrl(){
                if(this.localCustomer){
                    //  If we have the customer url via customer resource
                    return this.localCustomer['_links']['self']['href'];
                }else{
                    //  If we have the customer url via route
                    return decodeURIComponent(this.$route.params.customer_url);
                }
            },
        },
        methods: {
            closeCustomer(){

                //  If we have the customers
                if( this.customers.length ){

                    //  Notify parent to show customers list
                    this.$emit('close');

                }else{

                    /** Note that using router.push() or router.replace() does not allow us to make a
                     *  page refresh when visiting routes. This is undesirable at this moment since our
                     *  parent component contains the <router-view />. When the page does not refresh,
                     *  the <router-view /> is not able to receice the nested components defined in the
                     *  route.js file. This means that we are then not able to render the nested
                     *  components and present them. To counter this issue we must construct the
                     *  href and use "window.location.href" to make a hard page refresh.
                     */

                    var storeUrl = this.store['_links']['self'].href;

                    //  Set the route to view store customers
                    var route = {
                            name: 'show-store-customers',
                            params: {
                                store_url: encodeURIComponent(storeUrl)
                            }
                        };

                    //  Contruct the full path url
                    var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                    //  Visit the url
                    window.location.href = href;

                }
            },
            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async fetchCustomer() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingCustomer = true;

                //  Use the api call() function, refer to api.js
                await api.call('get', this.customerUrl)
                    .then(({data}) => {

                        //  Get the customer
                        self.localCustomer = data || null;

                        //  Stop loader
                        self.isLoadingCustomer = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isLoadingCustomer = false;

                    });
            },
        },
        created(){

            //  Fetch the customer
            this.fetchCustomer();

        }
    }
</script>
