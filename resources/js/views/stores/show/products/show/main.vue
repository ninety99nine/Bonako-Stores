<template>

    <Row :class="layoutSize == 'large' ? ['mt-4'] : []">

        <Col :span="22" :offset="1">

            <Row :gutter="12">

                <Col :span="24" :class=" layoutSize == 'large' ? ['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4'] : []">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="(isEditing && isLoadingProduct)" class="mb-2">Searching product</Loader>

                    <!-- Product Name, Statuses & Watch Video Button -->
                    <Row v-else-if="(isEditing && !isLoadingProduct && localProduct) || !isEditing" :gutter="12">

                        <Col :span="24">

                            <!-- If we are loading, Show Loader -->
                            <Loader v-if="!productForm" class="mb-2">Preparing product</Loader>

                            <Form v-else ref="productForm" :model="productForm" :rules="productFormRules" class="product-form">

                                <template v-if="!isVariationProduct">

                                    <!-- Toggle Visibility Switch -->
                                    <visibilitySwitch :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></visibilitySwitch>

                                </template>

                                <!-- Toggle Allow Variations Switch -->
                                <allowVariationsSwitch :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"
                                                       :parentFetchVariations="fetchVariations" :isEditing="isEditing"></allowVariationsSwitch>

                                <!-- Enter Name -->
                                <nameInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></nameInput>

                                <!-- If we allow variations -->
                                <template v-if="productForm.allow_variants">

                                    <!-- Variant Attributes -->
                                    <variantAttributes :productForm="productForm" :product="localProduct" :isLoading="isLoading"
                                                       :serverErrors="serverErrors" @generatedVariations="prepareProduct()"
                                                       @isCreatingVariations="isCreatingVariations = $event">
                                    </variantAttributes>

                                    <!-- Variations Heading -->
                                    <Divider orientation="left" class="font-weight-bold">Variations</Divider>

                                    <!-- Variations - Registered Globally -->
                                    <variations :product="localProduct" :isLoading="isLoading" :isCreatingVariations="isCreatingVariations"
                                                @isLoadingVariations="isLoadingVariations = $event">
                                    </variations>

                                </template>

                                <!-- If we don't allow variations -->
                                <template v-else>

                                    <!-- Select Type -->
                                    <typeSelectInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></typeSelectInput>

                                    <!-- Enter Description -->
                                    <descriptionInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></descriptionInput>

                                    <!-- Show Description Checkbox -->
                                    <showDescriptionCheckbox :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></showDescriptionCheckbox>

                                    <!-- Enter SKU & Barcode -->
                                    <Row :gutter="12">

                                        <Col :span="12">

                                            <!-- SKU Input -->
                                            <skuInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></skuInput>

                                        </Col>

                                        <Col :span="12">

                                            <!-- Barcode Input -->
                                            <barcodeInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></barcodeInput>

                                        </Col>

                                    </Row>

                                    <!-- Pricing Heading -->
                                    <Divider orientation="left" class="font-weight-bold mt-4">Pricing</Divider>

                                    <!-- Pricing -->
                                    <Row :gutter="12">

                                        <Col :span="8">

                                            <!-- Regular Price Input -->
                                            <regularPriceInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></regularPriceInput>

                                        </Col>

                                        <Col :span="8">

                                            <!-- Sale Price Input -->
                                            <salePriceInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></salePriceInput>

                                        </Col>

                                        <Col :span="8">

                                            <!-- Cost Price Input -->
                                            <costPriceInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></costPriceInput>

                                        </Col>

                                        <Col :span="24">

                                            <!-- Disclaimer: Free Product -->
                                            <Alert v-if="isFree" type="success" class="mt-2">
                                                This is a <span :class="['font-weight-bold', 'text-success']">FREE</span> product
                                            </Alert>

                                            <Alert v-if="!isFree && onSale" type="success" class="mt-2">
                                                <Row>
                                                    <Col :span="12">
                                                        Sale: <span :class="['font-weight-bold', 'text-success']">{{ salePercentage }}%</span>
                                                    </Col>
                                                    <Col :span="12">
                                                        Save: <span :class="['font-weight-bold', 'text-success']">{{ saleSavings }}</span>
                                                    </Col>
                                                </Row>
                                            </Alert>

                                        </Col>

                                    </Row>

                                    <!-- Quantities Heading -->
                                    <Divider orientation="left" class="font-weight-bold mt-4">Quantities</Divider>

                                    <Row :gutter="12">

                                        <Col :span="12">

                                            <!-- Allow Multiple Quantity Per Order Checkbox -->
                                            <allowMultipleQuantityPerOrderCheckbox :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowMultipleQuantityPerOrderCheckbox>

                                        </Col>

                                        <Col :span="12">

                                            <!-- Allow Multiple Quantity Per Order Checkbox -->
                                            <allowMaximumQuantityPerOrderCheckbox :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowMaximumQuantityPerOrderCheckbox>

                                            <!-- Multiple Quantity Per Order Input -->
                                            <maximumQuantityPerOrderInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></maximumQuantityPerOrderInput>

                                        </Col>

                                    </Row>

                                    <!-- Inventory Heading -->
                                    <Divider orientation="left" class="font-weight-bold mt-4">Inventory</Divider>

                                    <!-- Stock Management -->
                                    <Row :gutter="12">

                                        <Col :span="12">

                                            <!-- Allow Stock Management Checkbox -->
                                            <allowStockManagementCheckbox :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></allowStockManagementCheckbox>

                                            <template v-if="productForm.allow_stock_management">

                                                <!-- Stock Quantity Input -->
                                                <stockQuantityInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></stockQuantityInput>

                                            </template>

                                        </Col>

                                        <template v-if="productForm.allow_stock_management">

                                            <Col :span="12">

                                                <!-- Allow Stock Management Checkbox -->
                                                <autoManageStockCheckbox :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></autoManageStockCheckbox>

                                            </Col>

                                        </template>

                                    </Row>

                                    <!-- Locations -->
                                    <Row v-if="!isVariationProduct" :gutter="12">

                                        <Col :span="24">

                                            <!-- Select Locations -->
                                            <locationSelectInput :productForm="productForm" :isLoading="isLoading" :assignedLocations="assignedLocations"
                                                                 :location="location" :parentFetchProductLocations="fetchProductLocations"
                                                                 :serverErrors="serverErrors">
                                            </locationSelectInput>

                                        </Col>

                                    </Row>

                                </template>

                                <!-- If we are editting -->
                                <template v-if="isEditing">

                                    <!-- Save Changes Button -->
                                    <basicButton :disabled="(!productHasChanged || isSavingChanges)" :loading="isSavingChanges"
                                                 :ripple="(productHasChanged && !isSavingChanges)" type="success" size="large"
                                                 :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                        <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                                    </basicButton>

                                </template>

                                <!-- If we are creating -->
                                <template v-else>

                                    <!-- Create Button -->
                                    <basicButton :disabled="(!productHasChanged || isCreating)" :loading="isCreating"
                                                 :ripple="(productHasChanged && !isCreating)" type="success" size="large"
                                                 :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                        <span>{{ isCreating ? 'Creating...' : 'Create Product' }}</span>
                                    </basicButton>

                                </template>


                            </Form>

                        </Col>

                    </Row>

                    <!-- If we are not loading and don't have the product -->
                    <Alert v-else-if="(isEditing && !isLoadingProduct && !localProduct)" type="warning" class="mx-5" show-icon>
                        Product Not Found
                        <template slot="desc">
                        We could not get the product, try refreshing your browser. It's also possible that this product has been deleted.
                        </template>
                    </Alert>

                </Col>

            </Row>

        </Col>

    </Row>

</template>

<script>

    import skuInput from './components/skuInput.vue';
    import nameInput from './components/nameInput.vue';
    import statusTag from './../components/statusTag.vue';
    import barcodeInput from './components/barcodeInput.vue';
    import salePriceInput from './components/salePriceInput.vue';
    import costPriceInput from './components/costPriceInput.vue';
    import typeSelectInput from './components/typeSelectInput.vue';
    import descriptionInput from './components/descriptionInput.vue';
    import visibilitySwitch from './components/visibilitySwitch.vue';
    import regularPriceInput from './components/regularPriceInput.vue';
    import stockQuantityInput from './components/stockQuantityInput.vue';
    import locationSelectInput from './components/locationSelectInput.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import allowVariationsSwitch from './components/allowVariationsSwitch.vue';
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import variantAttributes from './components/variations/variantAttributes.vue';
    import showDescriptionCheckbox from './components/showDescriptionCheckbox.vue';
    import autoManageStockCheckbox from './components/autoManageStockCheckbox.vue';
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';
    import maximumQuantityPerOrderInput from './components/maximumQuantityPerOrderInput.vue';
    import allowStockManagementCheckbox from './components/allowStockManagementCheckbox.vue';
    import allowMaximumQuantityPerOrderCheckbox from './components/allowMaximumQuantityPerOrderCheckbox.vue';
    import allowMultipleQuantityPerOrderCheckbox from './components/allowMultipleQuantityPerOrderCheckbox.vue';


    export default {
        mixins: [miscMixin],
        components: {
            skuInput, nameInput, statusTag, barcodeInput, salePriceInput, costPriceInput, typeSelectInput,
            descriptionInput, visibilitySwitch, regularPriceInput, stockQuantityInput, locationSelectInput,
            allowVariationsSwitch, Loader, variantAttributes, showDescriptionCheckbox, autoManageStockCheckbox,
            basicButton, maximumQuantityPerOrderInput, allowStockManagementCheckbox, allowMaximumQuantityPerOrderCheckbox,
            allowMultipleQuantityPerOrderCheckbox
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
            product: {
                type: Object,
                default: null
            },
            products: {
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
                location_ids: [],
                productForm: null,
                isCreating: false,
                localProduct: null,
                serverErrors: null,
                productFormRules: {

                },
                isSavingChanges: false,
                isLoadingProduct: false,
                isLoadingLocations: false,
                isLoadingVariations: false,
                isCreatingVariations: false,
                productBeforeChanges: null,
            }
        },
        watch: {
            //  If the route changes
            $route (newVal, oldVal) {

                //  Prepare the product
                this.prepareProduct();

            }
        },
        computed: {
            isLoading(){
                return (
                    this.isLoadingProduct || this.isLoadingLocations || this.isLoadingVariations ||
                    this.isCreatingVariations || this.isCreating || this.isSavingChanges
                );
            },
            productUrl(){
                if(this.product){
                    //  If we have the product url via product resource
                    return this.product['_links']['self']['href'];
                }else{
                    //  If we have the product url via route
                    return decodeURIComponent(this.$route.params.product_url);
                }
            },
            createProductUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:products'].href
            },
            productLocationsUrl(){
                if( this.localProduct ){
                    return this.localProduct['_links']['bos:locations'].href;
                }
            },
            productVariationsUrl(){
                if( this.localProduct ){
                    return this.localProduct['_links']['bos:variations'].href;
                }
            },
            productHasChanged(){

                //  Check if the product has been modified
                var status = !_.isEqual(this.productForm, this.productBeforeChanges);

                return status;

            },
            isVariationProduct(){
                if( this.localProduct ){
                    return this.localProduct['parent_product_id'] ? true : false;
                }else{
                    return false;
                }
            },
            isEditing(){
                return this.product ? true : false;
            },
            isFree(){
                return this.productForm.unit_regular_price === 0 ||
                       (this.productForm.unit_regular_price === this.productForm.unit_sale_price)
            },
            onSale(){
                return (this.productForm.unit_sale_price > 0);
            },
            salePercentage(){

                var percentage = (this.productForm.unit_sale_price / this.productForm.unit_regular_price) * 100;

                return Math.round(percentage);
            },
            currencySymbol(){
                return (this.location._embedded.currency || {}).symbol;
            },
            saleSavings(){

                var saleSavings = (this.productForm.unit_regular_price - this.productForm.unit_sale_price);

                //  The formatPrice() method is defined from the miscMixin
                return this.formatPrice(saleSavings, this.currencySymbol);

            }
        },
        methods: {

            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async prepareProduct(){

                if( this.isEditing ){

                    //  Fetch the product
                    await this.fetchProduct();

                    this.$emit('fetchedProduct', this.localProduct);

                    //  If this product is not a variation
                    if( !this.isVariationProduct ){

                        //  Fetch the product locations
                        await this.fetchProductLocations();

                    }

                    //  If the product allows variations
                    if( (this.localProduct || {}).allow_variants ){

                        //  Fetch the product variations
                        await this.fetchVariations();

                    }

                }

                //  If this is not a variation product
                if( !this.isVariationProduct ){

                    //  Set the existing location ids otherwise set the current location as default location
                    this.location_ids = this.location_ids.length ? this.location_ids : [ this.location.id ];

                }

                //  Set the form details
                this.productForm = this.getProductForm();

                //  Save the form before any changes occur
                this.copyProductBeforeUpdate();

            },
            async fetchProduct() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingProduct = true;

                if( this.productUrl ){

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.productUrl)
                        .then(({data}) => {

                            //  Console log the data returned
                            console.log(data);

                            //  Get the product
                            self.localProduct = data || null;

                            //  Stop loader
                            self.isLoadingProduct = false;

                        })
                        .catch(response => {

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isLoadingProduct = false;

                        });
                }
            },
            async fetchProductLocations() {

                if( this.productLocationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingLocations = true;

                    //  Use the api call() function, refer to api.js
                    await api.call('get', this.productLocationsUrl)
                        .then(({data}) => {

                            //  Console log the data returned
                            console.log(data);

                            //  Stop loader
                            self.isLoadingLocations = false;

                            if( self.productForm ){

                                //  Set the locations
                                self.productForm.location_ids = ((data || [])['_embedded'] || [])['locations'].map((location) => {
                                    return location.id
                                });

                            }else{

                                //  Get the locations
                                self.location_ids = ((data || [])['_embedded'] || [])['locations'].map((location) => {
                                    return location.id
                                });

                            }

                        })
                        .catch(response => {

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            this.isLoadingLocations = false;

                        });

                }

            },
            async fetchVariations() {

                //  If we have the product url
                if( this.productVariationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingVariations = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.productVariationsUrl)
                        .then(({data}) => {

                            //  Console log the data returned
                            console.log(data);

                            //  Get the product variations
                            self.variations = data['_embedded']['products'] || [];

                            //  Stop loader
                            self.isLoadingVariations = false;

                        })
                        .catch(response => {

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isLoadingVariations = false;

                        });
                }
            },
            getProductForm(){

                //  Clone the product Object (if any) as a new Object
                return _.cloneDeep(Object.assign({},
                    //  Set the default form details
                    {

                        //  Product Management
                        name: '',
                        description: '',
                        show_description: false,
                        sku : null,
                        barcode: null,
                        visible: true,
                        location_ids: this.location_ids,
                        product_type_id: 1,

                        //  Variation Management
                        allow_variants: false,
                        variant_attributes: [],

                        //  Pricing Management
                        unit_regular_price: 0,
                        unit_sale_price : 0,
                        unit_cost: 0,

                        //  Quantity Management
                        allow_multiple_quantity_per_product: true,
                        allow_maximum_quantity_per_product : false,
                        maximum_quantity_per_product: 5,

                        //  Stock Management
                        allow_stock_management: false,
                        auto_manage_stock: true,
                        stock_quantity: 10,

                    //  Overide the default form details with the provided product Object
                    }, this.localProduct));

            },
            copyProductBeforeUpdate(){

                //  Clone the product before any changes occur
                this.productBeforeChanges = _.cloneDeep( this.productForm );

            },
            closeProduct(){

                //  If we have the products
                if( this.products.length ){

                    //  Notify parent to show products list
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

                    //  Set the route to view store products
                    var route = {
                            name: 'show-store-products',
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
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the product form
                this.$refs['productForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  If we are editing
                        if( this.isEditing ){

                            //  Attempt to save product
                            this.saveProduct();

                        }else{

                            //  Attempt to save product
                            this.createProduct();

                        }

                    //  If the validation failed
                    } else {
                        this.$Message.warning({
                            content: 'Sorry, you cannot update yet',
                            duration: 6
                        });
                    }
                })
            },
            saveProduct() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                /** Make an Api call to create the product. We include the
                 *  product details required for a new product creation.
                 */
                let productData = this.productForm;

                return api.call('put', this.productUrl, productData)
                    .then(({data}) => {

                        console.log(data);

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Product updated success message
                        self.$Message.success({
                            content: 'Your product has been updated!',
                            duration: 6
                        });

                        self.copyProductBeforeUpdate();

                        self.$emit('savedProduct', data);

                    }).catch((response) => {

                        console.log(response);

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Handle API Fail
                        this.handleApiFail(response);

                });
            },
            createProduct() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                //  Notify parent that this component is creating
                self.$emit('isCreating', self.isCreating);

                /** Make an Api call to create the product. We include the
                 *  product details required for a new product creation.
                 */
                let productData = this.productForm;

                return api.call('post', this.createProductUrl, productData)
                    .then(({data}) => {

                        console.log(data);

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent that this component is not creating
                        self.$emit('isCreating', self.isCreating);

                        //  Notify parent of the product created
                        self.$emit('createdProduct', data);

                        //  Product created success message
                        self.$Message.success({
                            content: 'Your product has been created!',
                            duration: 6
                        });

                        //  Reset the form
                        self.resetProductForm();

                    }).catch((response) => {

                        console.log(response);

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent that this component is not creating
                        self.$emit('isCreating', self.isCreating);

                        //  Handle API Fail
                        this.handleApiFail(response);

                });
            },
            handleApiFail(response){

                //  Get the error response data
                let data = (response || {}).data;

                //  Get the response errors
                var errors = (data || {}).errors;

                //  Set the general error message
                self.serverErrorMessage = (data || {}).message;

                /** 422: Validation failed. Incorrect credentials
                 */
                if((response || {}).status === 422){

                    //  If we have errors
                    if(_.size(errors)){

                        //  Foreach error
                        for (var i = 0; i < _.size(errors); i++) {

                            //  Get the error key e.g 'name', 'dedicated_short_code'
                            var prop = Object.keys(errors)[i];

                            //  Get the error value e.g 'The product name is required'
                            var value = Object.values(errors)[i][0];

                            //  Dynamically update the serverErrors for View UI to display the error on the appropriate form item
                            self.serverErrors[prop] = value;

                        }

                    }

                }
            },
            resetProductForm(){
                this.resetErrors();
                this.$refs['productForm'].resetFields();
            },
            resetErrors(){
                this.serverErrorMessage = '';
                this.serverErrors = [];
            },
        },
        created(){

            //  Prepare the product
            this.prepareProduct();

        }
    };

</script>
