<template>

    <Layout class="border-top" :style="{ minHeight:'100em' }">

        <Header :style="{ width: '100%' }" :class="['bg-white', 'border-top', 'border-bottom', 'p-0']">

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

                    <Input type="text" placeholder="Search order..." icon="ios-search-outline"></Input>

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
                            <span class="font-weight-bold text-primary">{{ visitShortCodeDialingCode }}</span>

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

        <!-- If we are loading, Show Loader -->
        <Loader v-show="isLoading" class="bg-white"></Loader>

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

                        <badge v-if="menuLink.total" :count="menuLink.total" type="success" class="float-right"></badge>

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
                                @refetchLocation="fetchAssignedLocations" @navigateToMenuLink="navigateToMenuLink" />

                </template>

            </Content>

        </Layout>

    </Layout>

</template>

<script>

    import basicButton from './../../../components/_common/buttons/basicButton.vue';
    import Loader from './../../../components/_common/loaders/default.vue';

    export default {
        components: { basicButton, Loader },
        data(){
            return {
                store: null,
                location: null,
                locationId: null,
                isLoadingStore: false,
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
                        icon: 'ios-cube-outline'
                    },
                    {
                        name: 'products',
                        linkName: '',
                        icon: 'ios-pricetags-outline'
                    },
                    {
                        name: 'customers',
                        linkName: '',
                        icon: 'ios-person-outline'
                    },
                    {
                        name: 'reports',
                        linkName: '',
                        icon: 'ios-pie-outline'
                    },
                    {
                        name: 'settings',
                        linkName: '',
                        icon: 'ios-settings-outline'
                    }
                ],
            }
        },
        watch: {

            //  Watch for changes on the location
            location: {
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
            locationUnfulfilledOrdersTotal(){
                return 50;
                //  return (this.location || {})['_links']['bos:received-unfulfilled-orders'].total;
            },
            storeUrl(){
                return decodeURIComponent(this.$route.params.store_url);
            },
            assignedLocationsUrl(){
                return (this.store || {})['_links']['bos:my-store-locations'].href;
            },
            defaultAssignedLocationsUrl(){
                return (this.store || {})['_links']['bos:my-store-default-location'].href;
            },
            hasVisitShortCode(){
                return this.store['_attributes']['has_visit_short_code'];
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
                }
            },
        },
        methods: {
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

                            //  Console log the data returned
                            console.log(data);

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

                            //  Log the responce
                            console.error(response);

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

                            //  Console log the data returned
                            console.log(data);

                            //  Get my store locations
                            self.assignedLocations = (((data || {})._embedded || {}).locations || []);

                            //  Fetch the user's default assigned location
                            self.fetchDefaultAssignedLocation();

                        })
                        .catch(response => {

                            //  Log the responce
                            console.error(response);

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

                            //  Console log the data returned
                            console.log(data);

                            //  Select the default location
                            self.location = (data || {});

                            //  Set the selected location id
                            self.locationId = self.location.id;

                            //  Stop loader
                            self.isLoadingLocations = false;

                        })
                        .catch(response => {

                            //  Log the responce
                            console.error(response);

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

                    this.updateData = {
                        location_id: this.locationId
                    };

                    for (let x = 0; x < this.assignedLocations.length; x++) {

                        //  Find the matching location
                        if( this.assignedLocations[x].id == this.locationId ){

                            //  Set the location
                            this.location = this.assignedLocations[x];

                        }

                    }

                    //  Use the api call() function, refer to api.js
                    api.call('put', this.defaultAssignedLocationsUrl, this.updateData)
                        .then(({data}) => {

                            //  Console log the data returned
                            console.log(data);

                            //  Stop loader
                            self.isLoadingLocations = false;

                        })
                        .catch(response => {

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isLoadingLocations = false;

                        });
                }
            },
            updateMenuLinks(){

                for (let x = 0; x < this.menuLinks.length; x++) {

                    //  If this is the orders menu
                    if( this.menuLinks[x].name == 'orders'){

                        //  Update total unfulfilled orders
                        this.menuLinks[x]['total'] = this.locationUnfulfilledOrdersTotal;

                    }

                }
            },
            navigateToMenuLink(linkName){

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
                var route = { name: linkName, params: {
                    store_url: encodeURIComponent(storeUrl) }
                };

                //  Contruct the full path url
                var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                //  Visit the url
                window.location.href = href;

            },
        },
        created(){

            //  Fetch the store
            this.fetchStore();

        }
    }
</script>
