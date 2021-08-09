<template>

    <Layout class="border-top" :style="{ minHeight:'100em' }">

        <!-- If we are loading, Show Loader -->
        <Loader v-show="isLoading" class="bg-white"></Loader>

        <Header v-if="!isLoading" :style="{ width: '100%' }" :class="['bg-white', 'border-top', 'border-bottom', 'p-0']">

            <Row :gutter="12">

                <Col :span="8" :offset="2">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isLoadingStore" :divStyles="{ textAlign: 'center' }"></Loader>

                    <!-- If we are not loading, Show the store breadcrumb -->
                    <Breadcrumb v-if="!isLoadingStore">

                        <!-- Link to stores -->
                        <BreadcrumbItem @click.native="navigateToMenuLink('show-stores')" class="cursor-pointer">
                            Stores
                        </BreadcrumbItem>

                        <!-- Link to current store -->
                        <BreadcrumbItem @click.native="navigateToMenuLink('show-store-overview')" class="cursor-pointer">
                            {{ storeName }}
                        </BreadcrumbItem>

                        <BreadcrumbItem>

                            <span class="clearfix">

                                <!-- Select location -->
                                <Poptip trigger="hover" content="Which location do you want to visit?" word-wrap>
                                    <Select v-model="locationId" placeholder="Select location" :disabled="isLoading"
                                            :style="{ fontWeight: 'normal !important' }"
                                            @on-change="updateDefaultAssignedLocation()">
                                        <Option v-for="(location, index) in assignedLocations"
                                                :value="location.id" :key="index">{{ location.name }}</Option>
                                    </Select>
                                </Poptip>

                                <!-- Refresh Locations Button -->
                                <Poptip trigger="hover" content="Refresh locations" word-wrap>
                                    <Button @click.native="fetchAssignedLocations()"
                                            :class="isLoadingLocations ? ['pr-2'] : ['px-2']"
                                            :loading="isLoadingLocations" :disabled="isLoadingLocations">
                                        <Icon v-show="!isLoadingLocations" type="ios-refresh" :size="20" class="mt-1"/>
                                    </Button>
                                </Poptip>

                            </span>

                        </BreadcrumbItem>

                    </Breadcrumb>

                </Col>

                <Col :span="6">

                    <Select :placeholder="location.name+': Search order'" icon="ios-search-outline" loading-text="Searching orders..."
                            not-found-text="No orders found" prefix="ios-search-outline" filterable :remote-method="searchOrder"
                            :loading="isSearching" @on-select="navigateToOrderLink($event)" :key="searchRenderKey">

                        <Option v-for="(order, index) in orders" :value="order._links.self.href" :key="index"
                                :label="'Order #'+order.number">

                            <div class="d-flex">
                                <Icon type="ios-cube-outline" :size="20" :class="['text-primary', 'mr-1']"/>
                                <div>
                                    <span :class="['font-weight-bold']">Order #{{ order.number }}</span>
                                    <span> - {{ order._embedded.customer._attributes.name }}</span>
                                    <div :style="{ fontSize: 'small' }" :class="['mt-1', 'text-black-50']">
                                        <span>{{ order._embedded.status.name }}</span>
                                        <span>{{ order._embedded.payment_status.name }}</span>
                                        <span>{{ order._embedded.delivery_status.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </Option>

                    </Select>

                </Col>

                <Col :span="4" class="clearfix">

                    <!-- If the store exists -->
                    <span v-if="store" :class="['float-right']">

                        <!-- Show the store details as a hoverable Poptip -->
                        <Poptip trigger="hover" word-wrap width="300">

                            <div slot="content" class="py-2">
                                <p>
                                    <span>Dial <span class="font-weight-bold text-primary">{{ visitShortCodeDialingCode }}</span> to visit store</span>
                                </p>
                            </div>

                            <!-- Show the info icon -->
                            <Icon type="ios-information-circle-outline" :size="16" />

                            <!-- Show the details text -->
                            <span>Dial </span>
                            <span class="font-weight-bold text-primary" :style="{ fontSize: '20px' }">{{ visitShortCodeDialingCode }}</span>

                        </Poptip>

                    </span>

                </Col>

                <Col :span="2" class="clearfix">

                    <Button type="success" size="default" :class="['float-right', 'mt-3']"
                            @click.native="fetchStores()" :loading="isLoadingStore"
                            :disabled="isLoadingStore">
                        <span>Subscribe</span>
                    </Button>

                </Col>

            </Row>

        </Header>

        <!-- If we are not loading and have the store -->
        <Layout v-if="!isLoading">

            <!-- Side Menu -->
            <Sider hide-trigger>

                <div class="w-100 bg-primary text-white bg-success font-weight-bold mt-3 mb-3 p-2">
                    <Icon type="ios-pin" class="mr-1" :size="20" />
                    <span>{{ location.name }}</span>
                </div>

                <!-- Show Store Menu Links -->
                <Menu :active-name="activeLink" theme="light" width="auto" key="store-menu">
                    <MenuItem v-for="(menuLink, index) in menuLinks" :key="index"

                        :name="menuLink.name" class="" @click.native="navigateToMenuLink(menuLink.linkName)">

                        <!-- Menu Icon -->
                        <Icon :type="menuLink.icon" :size="20" />

                        <!-- Menu Name -->
                        <span class="text-capitalize">{{ menuLink.name }}</span>

                        <Loader v-show="menuLink.total && isLoadingTotals" :imageStyles="{ width: '25px' }" :class="['float-right']" :showText="false"></Loader>

                        <!-- Menu Total -->
                        <badge v-if="menuLink.total && !isLoadingTotals" :count="menuLink.total" :type=" ['orders'].includes(menuLink.name) ? 'success' : 'normal'" class="float-right"></badge>

                    </MenuItem>
                </Menu>

            </Sider>

            <!-- Content -->
            <Content :style="{ overflow: 'visible' }">

                <!-- Place the custom route content here
                     We place the store views here. This includes views to show the store overview,
                     products, orders, locations, settings, and any more future views we may include.
                -->
                <template v-if="store && location">

                    <router-view :store="store" :location="location" :assignedLocations="assignedLocations"
                                 :locationTotals="locationTotals" @refetchLocationOrder="fetchAssignedLocations"
                                 @navigateToMenuLink="navigateToMenuLink" @navigateToRoute="navigateToRoute"
                                 @fetchLocationTotals="fetchLocationTotals" @updatedLocation="handleUpdatedLocation" />
                </template>

                <!-- If we are not loading and don't have the store -->
                <Alert v-else-if="!store" type="warning" class="mx-5" show-icon>
                    Store Not Found
                    <template slot="desc">
                    We could not get the store, try refreshing your browser. It's also possible that this store has been deleted.
                    </template>
                </Alert>

                <!-- If we are not loading and don't have the location -->
                <Alert v-else-if="!location" type="warning" class="mx-5" show-icon>
                    Location Not Found
                    <template slot="desc">
                    We could not get the location, try refreshing your browser. It's also possible that this loaction has been deleted.
                    </template>
                </Alert>

            </Content>

        </Layout>

    </Layout>

</template>

<script>

    import Loader from './../../../components/_common/loaders/default.vue';
    import basicButton from './../../../components/_common/buttons/basicButton.vue';

    export default {
        components: { basicButton, Loader },
        data(){
            return {
                orders: [],
                store: null,
                location: null,
                locationId: null,
                searchWord: null,
                searchRenderKey: 1,
                isSearching: false,
                searchTimeout: null,
                locationTotals: null,
                isLoadingStore: false,
                isLoadingTotals: false,
                isLoadingLocations: false,
                assignedLocations: [],
                menuLinks: [
                    {
                        name: 'overview',
                        linkName: 'show-store-overview',
                        icon: 'ios-analytics-outline'
                    },
                    {
                        name: 'orders',
                        linkName: 'show-store-orders',
                        icon: 'ios-cube-outline',
                        total: 0
                    },
                    {
                        name: 'products',
                        linkName: 'show-store-products',
                        icon: 'ios-pricetags-outline',
                        total: 0
                    },
                    {
                        name: 'coupons',
                        linkName: 'show-store-coupons',
                        icon: 'ios-pricetags-outline',
                        total: 0
                    },
                    {
                        name: 'instant carts',
                        linkName: 'show-store-instant-carts',
                        icon: 'ios-cart-outline',
                        total: 0
                    },
                    {
                        name: 'customers',
                        linkName: 'show-store-customers',
                        icon: 'ios-person-outline'
                    },
                    {
                        name: 'reports',
                        linkName: 'show-store-reports',
                        icon: 'ios-pie-outline'
                    },
                    {
                        name: 'settings',
                        linkName: 'show-store-location',
                        icon: 'ios-settings-outline'
                    },
                    {
                        name: 'feedback',
                        linkName: 'show-store-location',
                        icon: 'ios-settings-outline'
                    },
                ],
            }
        },
        watch: {

            //  Watch for changes on the locationTotals
            locationTotals: {
                handler: function (val, oldVal) {

                    this.updateMenuLinks();

                }
            },
        },
        computed: {
            isLoading(){
                return (this.isLoadingStore || this.isLoadingLocations);
            },
            storeName(){
                return (this.store || {}).name;
            },
            storeUrl(){
                return decodeURIComponent(this.$route.params.store_url);
            },
            orderUrl(){
                if( this.location ){
                    return (this.location || {})['_links']['bos:orders'].href;
                }
            },
            locationTotalsUrl(){
                if( this.location ){
                    return (this.location || {})['_links']['bos:totals'].href;
                }
            },
            assignedLocationsUrl(){
                if( this.store ){
                    return (this.store || {})['_links']['bos:my-store-locations'].href;
                }
            },
            defaultAssignedLocationsUrl(){
                if( this.store ){
                    return (this.store || {})['_links']['bos:my-store-default-location'].href;
                }
            },
            visitShortCode(){
                return (this.store['_attributes']['visit_short_code'] || {});
            },
            visitShortCodeDialingCode(){
                return this.visitShortCode.dialing_code;
            },
            activeLink(){
                //  Get the active menu link otherwise default to the overview page
                if( ['show-store-overview'].includes(this.$route.name) ){
                    return 'overview';
                }else if( ['show-store-orders'].includes(this.$route.name) ){
                    return 'orders';
                }else if( ['show-store-products'].includes(this.$route.name) ){
                    return 'products';
                }else if( ['show-store-coupons'].includes(this.$route.name) ){
                    return 'coupons';
                }else if( ['show-store-instant-carts'].includes(this.$route.name) ){
                    return 'instant carts';
                }else if( ['show-store-customers', 'show-store-customer'].includes(this.$route.name) ){
                    return 'customers';
                }else if( ['show-store-reports'].includes(this.$route.name) ){
                    return 'reports';
                }
            },
        },
        methods: {
            /**
             *  Search orders only 1 second after the user is done typing.
             */
            searchOrder: function (searchWord) {

                //  Reset the orders
                this.orders = [];

                //  If we have a search word
                if( searchWord ){

                    //  Clear the search timeout variable
                    clearTimeout(this.searchTimeout);

                    this.searchTimeout = setTimeout(() => {

                        //  Get the orders
                        this.fetchLocationOrders(searchWord);

                    }, 1000); // 1 sec delay

                }
            },
            fetchStore() {

                //  If we have the store url
                if( this.storeUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingStore = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.storeUrl)
                        .then(({data}) => {

                            //  Get the store
                            self.store = data || null;

                            //  Stop loader
                            self.isLoadingStore = false;

                            //  Fetch the user's assigned locations
                            self.fetchAssignedLocations();

                            //  Change dashboard heading
                            self.$emit('changeHeading', self.store.name);

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingStore = false;

                        });
                }
            },
            fetchAssignedLocations() {

                //  If we have assigned locations url
                if( this.assignedLocationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingLocations = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.assignedLocationsUrl)
                        .then(({data}) => {

                            //  Get my store locations
                            self.assignedLocations = (((data || {})._embedded || {}).locations || []);

                            //  Fetch the user's default assigned location
                            self.fetchDefaultAssignedLocation();

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingLocations = false;

                        });
                }
            },
            fetchDefaultAssignedLocation() {

                //  If we have the default assigned location url
                if( this.defaultAssignedLocationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.defaultAssignedLocationsUrl)
                        .then(({data}) => {

                            //  Select the default location
                            self.location = (data || {});

                            //  Set the selected location id
                            self.locationId = self.location.id;

                            //  If we have a default location
                            if(self.location){

                                //  Fetch location totals
                                self.fetchLocationTotals();

                            }

                            //  Stop loader
                            self.isLoadingLocations = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingLocations = false;

                        });
                }
            },
            updateDefaultAssignedLocation() {

                //  If we have the default assigned location url
                if( this.defaultAssignedLocationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    this.isLoadingLocations = true;

                    for (let x = 0; x < this.assignedLocations.length; x++) {

                        //  Find the matching location
                        if( this.assignedLocations[x].id == this.locationId ){

                            //  Set the location
                            this.location = this.assignedLocations[x];

                        }

                    }

                    let data = {
                        postData: {
                            location_id: this.locationId
                        }
                    };

                    //  Use the api call() function, refer to api.js
                    api.call('put', this.defaultAssignedLocationsUrl, data)
                        .then(({data}) => {

                            //  Fetch location totals
                            self.fetchLocationTotals();

                            //  Stop loader
                            self.isLoadingLocations = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingLocations = false;

                        });
                }
            },
            fetchLocationOrders(searchWord) {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSearching = true;

                //  Use the api call() function, refer to api.js
                api.call('get', this.orderUrl+'?search='+searchWord)
                    .then(({data}) => {

                        //  Get the orders
                        self.orders = (((data || {})._embedded || {}).orders || []);

                        //  Stop loader
                        self.isSearching = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isSearching = false;

                    });
            },
            fetchLocationTotals() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingTotals = true;

                //  Use the api call() locationTotalsUrl, refer to api.js
                api.call('get', this.locationTotalsUrl)
                    .then(({data}) => {

                        //  Get the locations totals
                        self.locationTotals = data;

                        //  Stop loader
                        self.isLoadingTotals = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isLoadingTotals = false;

                    });
            },
            updateMenuLinks(){

                for (let x = 0; x < this.menuLinks.length; x++) {

                    //  If this is the orders menu
                    if( this.menuLinks[x].name == 'orders' && this.locationTotals){

                        //  Update total undelivered orders
                        this.$set(this.menuLinks[x], 'total', this.locationTotals.orders.received.statuses.undelivered);

                    }

                    //  If this is the products menu
                    if( this.menuLinks[x].name == 'products' && this.locationTotals){

                        //  Update total undelivered products
                        this.$set(this.menuLinks[x], 'total', this.locationTotals.products.total);

                    }

                    //  If this is the coupons menu
                    if( this.menuLinks[x].name == 'coupons' && this.locationTotals){

                        //  Update total undelivered coupons
                        this.$set(this.menuLinks[x], 'total', this.locationTotals.coupons.total);

                    }

                    //  If this is the instant carts menu
                    if( this.menuLinks[x].name == 'instant carts' && this.locationTotals){

                        //  Update total undelivered instant carts
                        this.$set(this.menuLinks[x], 'total', this.locationTotals.instant_carts.total);

                    }

                }
            },
            navigateToOrderLink(event){

                //  Set the orderUrl
                var orderUrl = event.value;

                //  Reset the search
                this.orders = [];
                ++this.searchRenderKey;

                //  Navigate to the order
                this.navigateToMenuLink('show-store-order', orderUrl);

            },
            navigateToMenuLink(linkName, url){

                /** Note that using router.push() or router.replace() does not allow us to make a
                 *  page refresh when visiting routes. This is undesirable at this moment since our
                 *  current component contains the <router-view />. When the page does not refresh,
                 *  the <router-view /> is not able to receice the nested components defined in the
                 *  route.js file. This means that we are then not able to render the nested
                 *  components and present them. To counter this issue we must construct the
                 *  href and use "window.location.href" to make a hard page refresh.
                 */
                var storeUrl = this.store['_links']['self'].href;

                //  Add the "menu" query to our current store route
                var route = {
                        name: linkName,
                        params: {
                            store_url: encodeURIComponent(storeUrl)
                        }
                    };

                //  If we want to load a store order, then we must set the order url
                if( linkName === 'show-store-order'){

                    //  Ge the order url
                    var orderUrl = url;

                    //  Set the order url on the route
                    route.params.order_url = orderUrl;

                }

                this.navigateToRoute(route);

            },
            navigateToRoute(route){

                /** Note that using router.push() or router.replace() does not allow us to make a
                 *  page refresh when visiting routes. This is undesirable at this moment since our
                 *  current component contains the <router-view />. When the page does not refresh,
                 *  the <router-view /> is not able to receice the nested components defined in the
                 *  route.js file. This means that we are then not able to render the nested
                 *  components and present them. To counter this issue we must construct the
                 *  href and use "window.location.href" to make a hard page refresh.
                 */

                //  Contruct the full path url
                var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                //  Visit the url
                window.location.href = href;

            },
            handleUpdatedLocation(location){
                this.location = location;
            }
        },
        created(){

            //  Fetch the store
            this.fetchStore();

        }
    }
</script>
