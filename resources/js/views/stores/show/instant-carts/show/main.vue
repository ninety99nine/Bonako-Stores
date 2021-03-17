<template>

    <Row :class="layoutSize == 'large' ? ['mt-4'] : []">

        <Col :span="22" :offset="1">

            <Row :gutter="12">

                <Col :span="24" :class=" layoutSize == 'large' ? ['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4'] : []">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="(isEditing && isLoadingInstantCart)" class="mb-2">Searching instant cart</Loader>

                    <!-- Instant Cart Name, Statuses & Watch Video Button -->
                    <Row v-else-if="(isEditing && !isLoadingInstantCart && localInstantCart) || !isEditing" :gutter="12">

                        <Col :span="24">

                            <!-- If we are loading, Show Loader -->

                            <Loader v-if="isCreating" class="mb-2">Creating instant cart</Loader>
                            <Loader v-else-if="isSavingChanges" class="mb-2">Saving instant cart</Loader>
                            <Loader v-else-if="!instantCartForm" class="mb-2">Preparing instant cart</Loader>

                            <Form v-if="instantCartForm" ref="instantCartForm" :model="instantCartForm" :rules="instantCartFormRules">

                                <!-- Toggle Active Switch -->
                                <activeSwitch :instantCartForm="instantCartForm" :isLoading="isLoading" :serverErrors="serverErrors" class="mb-2"></activeSwitch>

                                <!-- Enter Name -->
                                <nameInput :instantCartForm="instantCartForm" :isLoading="isLoading" :serverErrors="serverErrors"></nameInput>

                                <!-- Enter Description -->
                                <descriptionInput :instantCartForm="instantCartForm" :isLoading="isLoading" :serverErrors="serverErrors"></descriptionInput>

                                <!-- Allow Free Delivery Checkbox -->
                                <allowFreeDeliveryCheckbox :instantCartForm="instantCartForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowFreeDeliveryCheckbox>

                                <!-- Free Delivery Checkbox Alert -->
                                <freeDeliveryAlert :instantCartForm="instantCartForm" :isLoading="isLoading" :serverErrors="serverErrors"></freeDeliveryAlert>

                                <!-- Products Heading -->
                                <Divider orientation="left" class="font-weight-bold">Products</Divider>

                                <!-- Select Products -->
                                <productSelectInput :instantCartForm="instantCartForm" :isLoadingProducts="isLoadingProducts"
                                                    :isLoading="isLoading" :products="products" :parentFetchLocationProducts="fetchLocationProducts"
                                                    :serverErrors="serverErrors">
                                </productSelectInput>

                                <!-- Coupons Heading -->
                                <Divider orientation="left" class="font-weight-bold mt-4">Coupons</Divider>

                                <!-- Select Coupons -->
                                <couponSelectInput :instantCartForm="instantCartForm" :isLoadingCoupons="isLoadingCoupons"
                                                    :isLoading="isLoading" :coupons="coupons" :parentFetchLocationCoupons="fetchLocationCoupons"
                                                    :serverErrors="serverErrors">
                                </couponSelectInput>

                                <!-- Locations Heading -->
                                <Divider orientation="left" class="font-weight-bold mt-4">Locations</Divider>

                                <!-- If we are editting -->
                                <template v-if="isEditing">

                                    <!-- Save Changes Button -->
                                    <basicButton :disabled="(!instantCartHasChanged || isSavingChanges)" :loading="isSavingChanges"
                                                 :ripple="(instantCartHasChanged && !isSavingChanges)" type="success" size="large"
                                                 :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                        <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                                    </basicButton>

                                </template>

                                <!-- If we are creating -->
                                <template v-if="!isEditing">

                                    <!-- Create Button -->
                                    <basicButton :disabled="(!instantCartHasChanged || isCreating)" :loading="isCreating"
                                                 :ripple="(instantCartHasChanged && !isCreating)" type="success" size="large"
                                                 :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                        <span>{{ isCreating ? 'Creating...' : 'Create Instant Cart' }}</span>
                                    </basicButton>

                                </template>


                            </Form>

                        </Col>

                    </Row>

                    <!-- If we are not loading and don't have the instant cart -->
                    <Alert v-else-if="(isEditing && !isLoadingInstantCart && !localInstantCart)" type="warning" class="mx-5" show-icon>
                        Instant Cart Not Found
                        <template slot="desc">
                        We could not get the instant cart, try refreshing your browser. It's also possible that this instant cart has been deleted.
                        </template>
                    </Alert>

                </Col>

            </Row>

        </Col>
    </Row>

</template>

<script>

    import nameInput from './components/nameInput.vue';
    import activeSwitch from './components/activeSwitch.vue';
    import descriptionInput from './components/descriptionInput.vue';
    import couponSelectInput from './components/couponSelectInput.vue';
    import freeDeliveryAlert from './components/freeDeliveryAlert.vue';
    import productSelectInput from './components/productSelectInput.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import allowFreeDeliveryCheckbox from './components/allowFreeDeliveryCheckbox.vue';
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';

    export default {
        mixins: [miscMixin],
        components: {
            nameInput, activeSwitch, descriptionInput, couponSelectInput, freeDeliveryAlert,
            productSelectInput, Loader, basicButton, allowFreeDeliveryCheckbox
        },
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
            instantCart: {
                type: Object,
                default: null
            },
            instantCarts: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            layoutSize: {
                type: String,
                default: 'large'
            },
        },
        data(){
            return {
                coupons: [],
                products: [],
                isCreating: false,
                instantCartForm: null,
                localInstantCart: null,
                instantCartFormRules: {

                },
                isSavingChanges: false,
                isLoadingCoupons: false,
                isLoadingProducts: false,
                isLoadingInstantCart: false,
                instantCartBeforeChanges: null,
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  Prepare the instant cart
                this.prepareInstantCart();

            }
        },
        computed: {
            isLoading(){
                return (
                    this.isLoadingInstantCart || this.isLoadingCoupons || this.isLoadingProducts ||
                    this.isCreating || this.isSavingChanges
                );
            },
            instantCartUrl(){
                if(this.instantCart){

                    //  If we have the instant cart url via instant cart resource
                    return this.instantCart['_links']['self']['href'];

                }else{

                    //  If we have the instant cart url via route
                    return decodeURIComponent(this.$route.params.instant_cart_url);

                }
            },
            createInstantCartUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:instant_carts'].href
            },
            locationProductsUrl(){
                if( this.location ){
                    return this.location['_links']['bos:products'].href;
                }
            },
            locationCouponsUrl(){
                if( this.location ){
                    return this.location['_links']['bos:coupons'].href;
                }
            },
            instantCartHasChanged(){

                //  Check if the instant cart has been modified
                var status = !_.isEqual(this.instantCartForm, this.instantCartBeforeChanges);

                return status;

            },
            isEditing(){
                return this.instantCart ? true : false;
            }
        },
        methods: {

            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async prepareInstantCart(){

                if( this.isEditing ){

                    //  Reset tthe instant cart form
                    this.instantCartForm = null;

                    //  Fetch the instant cart
                    await this.fetchInstantCart();

                    //  Notify parent of fetched instant cart
                    this.$emit('fetchedInstantCart', this.localInstantCart);

                }

                //  Fetch the instant cart products
                await this.fetchLocationProducts();

                //  Fetch the instant cart coupons
                await this.fetchLocationCoupons();

                //  Set the form details
                this.instantCartForm = this.getInstantCartForm();

                //  Save the form before any changes occur
                this.copyInstantCartBeforeUpdate();

            },
            async fetchInstantCart() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingInstantCart = true;

                if( this.instantCartUrl ){

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.instantCartUrl)
                        .then(({data}) => {

                            //  Get the instant cart
                            self.localInstantCart = data || null;

                            //  Stop loader
                            self.isLoadingInstantCart = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingInstantCart = false;

                        });
                }
            },
            async fetchLocationProducts() {

                if( this.locationProductsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingProducts = true;

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.locationProductsUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoadingProducts = false;

                            //  Set the products
                            self.products = (((data || [])['_embedded'] || [])['products'] || []).map((product) => {

                                //  Assign default quantity
                                product.quantity = 1;

                                //  Return product
                                return product;
                            });

                        })
                        .catch(response => {

                            //  Stop loader
                            this.isLoadingProducts = false;

                        });

                }

            },
            async fetchLocationCoupons() {

                if( this.locationCouponsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingCoupons = true;

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.locationCouponsUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoadingCoupons = false;

                            //  Set the coupons
                            self.coupons = (((data || [])['_embedded'] || [])['coupons'] || []);

                        })
                        .catch(response => {

                            //  Stop loader
                            this.isLoadingCoupons = false;

                        });

                }

            },
            getInstantCartForm(){

                //  Clone the instant cart Object (if any) as a new Object
                var form = _.cloneDeep(Object.assign({},
                    //  Set the default form details
                    {
                        //  Instant Cart Management
                        name: '',
                        active: true,
                        description: '',
                        coupons: [],
                        items: [],
                        delivery_fee: 0,
                        allow_free_delivery: false,
                        location_id: this.location.id,

                        //  Pricing Management
                        currency: this.locationCurrencyCode

                    //  Overide the default form details with the provided instant cart Object
                    }, this.localInstantCart));

                    if( this.localInstantCart ){

                        form.active = this.localInstantCart.active.status;

                        //  Set the coupons
                        form.coupons = (this.localInstantCart['_embedded']['cart']['_embedded']['coupon_lines']).map((coupon_line) => {
                            return {
                                id: coupon_line.coupon_id
                            }
                        });

                        //  Set the items
                        form.items = (this.localInstantCart['_embedded']['cart']['_embedded']['item_lines']).map((item_line) => {
                            return {
                                id: item_line.product_id,
                                name: item_line.name,
                                quantity: item_line.quantity
                            }
                        });

                    }

                return form;

            },
            copyInstantCartBeforeUpdate(){

                //  Clone the instant cart before any changes occur
                this.instantCartBeforeChanges = _.cloneDeep( this.instantCartForm );

            },
            closeInstantCart(){

                //  If we have the instant carts
                if( this.instantCarts.length ){

                    //  Notify parent to show instant carts list
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

                    //  Set the route to view store instant carts
                    var route = {
                            name: 'show-store-instant-carts',
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
            handleUpdatedProductQuantity(updatedProducts){

                //  Update the instant cart products
                this.$set(this.instantCartForm, 'products', updatedProducts);

                for (let x = 0; x < this.products.length; x++) {

                    for (let y = 0; y < updatedProducts.length; y++) {

                        if(this.products[x].id == updatedProducts[y].id){

                            //  Set the product quantity to the updated quantity
                            this.$set(this.products[x], 'quantity', updatedProducts[y].quantity);

                        }

                    }

                }

                //  Reset the product selection key to force a re-render of the component
                ++this.resetProductSelectorKey;

            },
            async handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the form
                this.$refs['instantCartForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  If we are editing
                        if( this.isEditing ){

                            //  Attempt to save instant cart
                            this.saveInstantCart();

                        //  If we are creating
                        }else{

                            //  Attempt to create instant cart
                            this.createInstantCart();

                        }

                    //  If the validation failed
                    } else {

                        //  If we are editing
                        if( this.isEditing ){

                            this.$Message.warning({
                                content: 'Sorry, you cannot update instant cart yet',
                                duration: 6
                            });

                        //  If we are creating
                        }else{

                            this.$Message.warning({
                                content: 'Sorry, you cannot create instant cart yet',
                                duration: 6
                            });

                        }

                    }
                });

            },
            saveInstantCart() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                /** Make an Api call to create the instant cart. We include the
                 *  instant cart details required for a new instant cart creation.
                 */
                let data = {
                    postData: this.instantCartForm,
                };

                api.call('put', this.instantCartUrl, data, this)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Instant cart updated success message
                        self.$Message.success({
                            content: 'Your instant cart has been updated!',
                            duration: 6
                        });

                        self.copyInstantCartBeforeUpdate();

                        //  Notify parent on changes
                        self.$emit('savedInstantCart', data);

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot update instant cart',
                            duration: 6
                        });

                        //  Stop loader
                        self.isSavingChanges = false;

                });
            },
            createInstantCart() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                /** Make an Api call to create the instant cart. We include the
                 *  instant cart details required for a new instant cart creation.
                 */
                let data = {
                        postData: this.instantCartForm
                    };

                api.call('post', this.createInstantCartUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent of the instant cart created
                        self.$emit('createdInstantCart', data);

                        //  Instant cart created success message
                        self.$Message.success({
                            content: 'Your instant cart has been created!',
                            duration: 6
                        });

                        //  resetForm() declared in miscMixin
                        self.resetForm('instantCartForm');

                        //  Close Drawer
                        self.$emit('closeDrawer');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot create instant cart',
                            duration: 6
                        });

                        //  Stop loader
                        self.isCreating = false;

                });
            },
        },
        created(){

            //  Prepare the instant cart
            this.prepareInstantCart();

        }
    };

</script>
