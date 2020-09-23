<template>
    <Layout class="border-top" :style="{ minHeight:'100em' }">

        <Header :style="{width: '100%'}" class="bg-white border-top border-bottom p-0">

            <Row :gutter="12">

                <Col :span="12" :offset="2">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isLoadingStore" :divStyles="{ textAlign: 'center' }"></Loader>

                    <!-- If we are not loading, Show the store breadcrumb -->
                    <Breadcrumb v-else>

                        <!-- Link to stores -->
                        <BreadcrumbItem @click.native="navigateToStoreLink('show-stores')" class="cursor-pointer">
                            Stores
                        </BreadcrumbItem>

                        <!-- Link to current store -->
                        <BreadcrumbItem @click.native="navigateToStoreLink('show-store-overview')" class="cursor-pointer">
                            {{ storeName }}
                        </BreadcrumbItem>

                        <!-- Link to locations -->
                        <template v-if="isViewingLocations">
                            <BreadcrumbItem @click.native="navigateToStoreLink('show-locations')" class="cursor-pointer">Locations</BreadcrumbItem>
                        </template>

                        <!-- Link to current location -->
                        <template v-if="location">
                            <BreadcrumbItem>{{ location.name }}</BreadcrumbItem>
                        </template>

                    </Breadcrumb>
                    
                </Col>

                <Col :span="4">

                    <!-- If the store exists -->
                    <span v-if="store">
                        
                        <!-- Show the store details as a hoverable Poptip -->
                        <Poptip trigger="hover" word-wrap width="300">

                            <div slot="content" class="py-2" :style="{ lineHeight: 'normal' }">
                                <p>
                                    <span>Locations:</span> <span class="font-weight-bold">{{ totalLocations }}</span>
                                </p>
                            </div>

                            <!-- Show the details text -->
                            <span>Store Details: </span>

                            <!-- Show the info icon -->
                            <Icon type="ios-information-circle-outline" :size="16" />
                        
                        </Poptip>

                    </span>
                    
                </Col>

                <Col :span="4" class="clearfix">

                    <!-- Save Changes Button
                         If we have unsaved changes then show the green ripple effect and allow the button to be clickable
                         otherwise turn off the ripple effect and disable the button
                     -->
                    <basicButton v-if="store" :disabled="(!hasUnsavedChanges || isSavingChanges)" :loading="isSavingChanges" :ripple="(hasUnsavedChanges && !isSavingChanges)" 
                                  type="success" size="large" class="float-right" @click.native="handleSaveChanges">
                        <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                    </basicButton>
                    
                </Col>

            </Row>

        </Header>

        <!-- If we are loading -->
        <template v-if="isLoadingStore">

            <!-- Show Loader -->
            <Loader class="mt-5"></Loader>

        </template>

        <!-- If we are not loading and have the store -->
        <Layout v-else-if="store">

            <!-- Side Menu -->
            <Sider hide-trigger>

                <template v-if="!isLoadingStore && !isLoadingLocation">

                    <!-- If we are viewing a specific location -->
                    <template v-if="location">

                        <div class="w-100 bg-primary text-white bg-success font-weight-bold mt-3 mb-3 p-2">
                            <Icon type="ios-pin" class="mr-1" :size="20" />
                            <span>{{ location.name }}</span>
                        </div>

                        <!-- Show Location Menu Links -->
                        <Menu :active-name="activeLink" theme="light" width="auto" key="location-menu">
                            <MenuItem v-for="(menuLink, index) in locationMenuLinks" :key="index"
                                :name="menuLink.name" class="" @click.native="navigateToLocationLink(menuLink.linkName)">
                                <!-- Menu Icon -->
                                <Icon :type="menuLink.icon" :size="20" />
                                <!-- Menu Name -->
                                <span class="text-capitalize">{{ menuLink.name }}</span>
                            </MenuItem>
                        </Menu>
                        
                    </template>

                    <!-- If we are not viewing a specific location -->
                    <template v-else>

                        <div class="w-100 bg-primary text-white bg-success font-weight-bold mt-3 mb-3 p-2">
                            <Icon type="ios-pin" class="mr-1" :size="20" />
                            <span>{{ store.name }}</span>
                        </div>

                        <!-- Show Store Menu Links -->
                        <Menu :active-name="activeLink" theme="light" width="auto" key="store-menu">
                            <MenuItem v-for="(menuLink, index) in storeMenuLinks" :key="index"
                                :name="menuLink.name" class="" @click.native="navigateToStoreLink(menuLink.linkName)">
                                <!-- Menu Icon -->
                                <Icon :type="menuLink.icon" :size="20" />
                                <!-- Menu Name -->
                                <span class="text-capitalize">{{ menuLink.name }}</span>
                            </MenuItem>
                        </Menu>
                        
                    </template>

                </template>

            </Sider>

            <!-- Content -->
            <Content>
        
                <!-- Place the custom route content here 
                    We place the store views here. This includes views to show the store overview,
                    products, orders, locations, settings, and any more future views we may include.

                    Explanation:

                    :requestToSaveChanges: This is a property that the the nested child component must watch
                        in order to know when it can save changes detected/communicated by this component.
                        This can be used to let the nested child component to know when it can commit to
                        save changes.

                    @unsavedChanges: This is an event from the nested child component that informs this component
                        that we have unsaved changes that must be saved. This can be used to disable/enable the
                        "Save Changes" button

                    @isSaving: This is an event from the nested child component that informs this component that
                        the child component is saving the changes. It returns a true or false status so that this
                        component is aware of whether we are still saving or not. This can be used to disable the
                        "Save Changes" button
                -->
                <template v-if="!isLoadingStore && !isLoadingLocation">

                    <router-view :store="store" :location="location" :requestToSaveChanges="requestToSaveChanges" 
                                @updatedStore="handleUpdatedStore" @unsavedChanges="handleUnsavedChanges" 
                                @isSaving="handlesIsSaving"/>

                </template>
                    
            </Content>

        </Layout>
                    
        <!-- If we are not loading and don't have the store -->
        <template v-else-if="!store">

            <Alert type="warning" class="m-5" show-icon>
                Store Not Found
                <template slot="desc">
                We could not get your store, try refreshing your browser. It's also possible that this store has been deleted.
                </template>
            </Alert>

        </template>

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
                isLoadingStore: false,
                isSavingChanges: false,
                requestToSaveChanges: 0,
                isLoadingLocation: false,
                hasUnsavedChanges: false,
                storeMenuLinks: [
                    {
                        name: 'overview',
                        linkName: 'show-store-overview',
                        icon: 'ios-analytics-outline'
                    },
                    {
                        name: 'locations',
                        linkName: 'show-locations',
                        icon: 'ios-git-branch'
                    },
                    {
                        name: 'products',
                        linkName: '',
                        icon: 'ios-basket-outline'
                    },
                    {
                        name: 'Instant Carts',
                        linkName: 'show-store-instant-carts',
                        icon: 'ios-basket-outline'
                    },
                    {
                        name: 'orders',
                        linkName: '',
                        icon: 'ios-stats-outline'
                    },
                    {
                        name: 'users',
                        linkName: '',
                        icon: 'ios-person-outline'
                    },
                    {
                        name: 'analytics',
                        linkName: '',
                        icon: 'ios-trending-up'
                    }
                ],
                locationMenuLinks: [
                    {
                        name: 'overview',
                        linkName: 'show-location',
                        icon: 'ios-analytics-outline'
                    },
                    {
                        name: 'products',
                        linkName: 'show-location-products',
                        icon: 'ios-basket-outline'
                    },
                    {
                        name: 'Instant Carts',
                        linkName: 'show-location-instant-carts',
                        icon: 'ios-basket-outline'
                    },
                    {
                        name: 'orders',
                        linkName: '',
                        icon: 'ios-stats-outline'
                    },
                    {
                        name: 'users',
                        linkName: '',
                        icon: 'ios-person-outline'
                    },
                    {
                        name: 'analytics',
                        linkName: '',
                        icon: 'ios-trending-up'
                    }
                ]
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  If we have the location url
                if( newVal.params.location_url ){

                    //  Fetch the location
                    this.fetchLocation();

                }

            }
        },
        computed: {
            isViewingLocations(){
                //  Check if we are viewing the store locations
                if( ['show-locations', 'show-location'].includes(this.$route.name) ){
                    return true;
                }
                return false
            },
            activeLink(){
                //  Get the active menu link otherwise default to the overview page
                if( ['show-store-overview', 'show-location'].includes(this.$route.name) ){
                    return 'overview';
                }else if( ['show-locations', 'show-location'].includes(this.$route.name) ){
                    return 'locations';
                }
            },
            locationUrl(){
                return decodeURIComponent(this.$route.params.location_url);
            },
            storeUrl(){
                return decodeURIComponent(this.$route.params.store_url);
            },
            storeName(){
                return (this.store || {}).name;
            },
            totalLocations(){
                return this.store['_links']['bos:locations'].total;
            },
        },
        methods: {
            handleUnsavedChanges(status){
                //  status is true/false
                this.hasUnsavedChanges = status;
            },
            handleSaveChanges(){
                //  If we have unsaved changes
                if( this.hasUnsavedChanges ){
                    ++this.requestToSaveChanges;
                }
            },
            handlesIsSaving(status){
                this.isSavingChanges = status;
            },
            handleSelectedLocation(location){
                alert('handleSelectedLocation');
                this.location = Object.assign({}, location);
            },
            handleUpdatedStore(store){
                this.store = Object.assign({}, store);

                this.$emit('changeHeading', this.store.name)
            },
            navigateToStoreLink(linkName){

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

                this.location = null;

            },
            navigateToLocationLink(linkName){

                /** Note that using router.push() or router.replace() does not allow us to make a
                 *  page refresh when visiting routes. This is undesirable at this moment since our 
                 *  current component contains the <router-view />. When the page does not refresh, 
                 *  the <router-view /> is not able to receice the nested components defined in the 
                 *  route.js file. This means that we are then not able to render the nested 
                 *  components and present them. To counter this issue we must construct the 
                 *  href and use "window.location.href" to make a hard page refresh.
                 */
                var storeUrl = this.store['_links']['self'].href;
                var locationUrl = this.location['_links']['self'].href;

                //  Add the "menu" query to our current store route
                var route = { name: linkName, params: {
                        store_url: encodeURIComponent(storeUrl),
                        location_url: encodeURIComponent(locationUrl)
                    }
                };

                //  Contruct the full path url
                var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                //  Visit the url
                window.location.href = href;

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
                            
                            //  Console log the data returned
                            console.log(data);

                            //  Get the store
                            self.store = data || null;

                            //  Stop loader
                            self.isLoadingStore = false;

                            self.$emit('changeHeading', self.store.name)

                        })         
                        .catch(response => { 

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isLoadingStore = false;

                        });
                }
            },
            fetchLocation() {

                //  If we have the location url
                if( this.locationUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingLocation = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.locationUrl)
                        .then(({data}) => {
                            
                            //  Console log the data returned
                            console.log(data);

                            //  Get the location
                            self.location = data || null;

                            //  Stop loader
                            self.isLoadingLocation = false;

                        })         
                        .catch(response => { 

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isLoadingLocation = false;

                        });
                }
            }
        },
        created(){

            //  Fetch the store
            this.fetchStore();

            //  If we have the location url
            if( this.$route.params.location_url ){

                //  Fetch the location
                this.fetchLocation();

            }
            
        }
    }
</script>
