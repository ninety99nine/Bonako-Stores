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

                            <Loader v-if="isCreating" class="mb-2">Creating product</Loader>
                            <Loader v-else-if="isSavingChanges" class="mb-2">Saving product</Loader>
                            <Loader v-else-if="!productForm" class="mb-2">Preparing product</Loader>

                            <Form v-if="productForm" ref="productForm" :model="productForm" :rules="productFormRules" class="product-form">

                                <!-- Toggle Visibility Switch -->
                                <visibilitySwitch :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></visibilitySwitch>

                                <!-- Toggle Allow Variations Switch -->
                                <allowVariationsSwitch :productForm="productForm" :isLoading="isLoading"
                                                       :serverErrors="serverErrors" :isEditing="isEditing"
                                                       :productHasChanged="productHasChanged"
                                                       @restoreProductForm="restoreProductForm">
                                </allowVariationsSwitch>

                                <!-- Enter Name -->
                                <nameInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></nameInput>

                                <!-- Select Type -->
                                <typeSelectInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></typeSelectInput>

                                <!-- Enter Description -->
                                <descriptionInput :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></descriptionInput>

                                <!-- Show Description Checkbox -->
                                <showDescriptionCheckbox :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></showDescriptionCheckbox>

                                <!-- If we allow variations -->
                                <template v-if="productForm.allow_variants">

                                    <!-- Variant Attributes -->
                                    <variantAttributes :productForm="productForm" :product="localProduct" :isLoading="isLoading" :variations="variations"
                                                       :serverErrors="serverErrors" @variantAttributesHasChanged="variantAttributesHasChanged = $event"
                                                       @isCreatingVariations="isCreatingVariations = $event"
                                                       @generatedVariations="handleGeneratedVariations()">
                                    </variantAttributes>

                                    <!-- Variations Heading -->
                                    <Divider v-if="!isLoadingVariations && !isCreatingVariations" orientation="left" class="font-weight-bold">Variations</Divider>

                                    <!-- Variations - Registered Globally -->
                                    <variations :location="location" :product="localProduct" :isLoading="isLoading" :isCreatingVariations="isCreatingVariations"
                                                @isLoadingVariations="isLoadingVariations = $event" @variations="variations = $event">
                                    </variations>

                                </template>

                                <!-- If we don't allow variations -->
                                <template v-else>

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

                                        <Col :span="24">

                                            <!-- Show Description Checkbox -->
                                            <isFreeCheckbox :productForm="productForm" :isLoading="isLoading" :serverErrors="serverErrors"></isFreeCheckbox>

                                        </Col>

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

                                            <Alert v-if="noPrice" type="warning" class="mt-2">
                                                Enter your product price
                                            </Alert>

                                            <Alert v-if="!isFree && onSale" type="success" class="mt-2">
                                                <Row>
                                                    <Col :span="8">
                                                        <!-- Sale Explanation Poptip -->
                                                        <Poptip trigger="hover" placement="top-start" word-wrap width="300"
                                                                content="This is the percentage saved by the customer buying this product">

                                                            Sale: <span :class="['font-weight-bold', 'text-success']">{{ salePercentage }}% off</span>

                                                            <!-- Show the info icon -->
                                                            <Icon type="ios-information-circle-outline" :size="16" />

                                                        </Poptip>
                                                    </Col>
                                                    <Col :span="8">
                                                        <!-- Save Explanation Poptip -->
                                                        <Poptip trigger="hover" placement="top" word-wrap width="300"
                                                                content="This is the amount saved by the customer buying this product">

                                                            Save: <span :class="['font-weight-bold', 'text-success']">{{ saleSavings }}</span>

                                                            <!-- Show the info icon -->
                                                            <Icon type="ios-information-circle-outline" :size="16" />

                                                        </Poptip>
                                                    </Col>
                                                    <Col :span="8">
                                                        <!-- Profit Explanation Poptip -->
                                                        <Poptip trigger="hover" placement="top-end" word-wrap width="300"
                                                                content="This is the profit made by selling this product">

                                                            Profit: <span :class="['font-weight-bold', 'text-success']">{{ profit }}</span>

                                                            <!-- Show the info icon -->
                                                            <Icon type="ios-information-circle-outline" :size="16" />

                                                        </Poptip>
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

                                    <Alert v-if="quantityDescription" type="success" class="mt-2">{{ quantityDescription }}</Alert>

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

                                    <Alert :type="stockStatus.type" class="mt-2">{{ stockStatus.text }}</Alert>

                                    <template v-if="!isVariationProduct">

                                        <!-- Locations Heading -->
                                        <Divider orientation="left" class="font-weight-bold mt-4">Locations</Divider>

                                        <!-- Locations -->
                                        <Row :gutter="12">

                                            <Col :span="24">

                                                <!-- Select Locations -->
                                                <locationSelectInput :productForm="productForm" :isLoading="isLoading" :assignedLocations="assignedLocations"
                                                                    :location="location" :parentFetchProductLocations="fetchProductLocations"
                                                                    :serverErrors="serverErrors">
                                                </locationSelectInput>

                                            </Col>

                                        </Row>

                                    </template>

                                </template>

                                <!-- If we are editting and not using variations -->
                                <template v-if="isEditing">

                                    <!-- Save Changes Button -->
                                    <basicButton :disabled="(!productHasChanged || isSavingChanges || variantAttributesHasChanged)" :loading="isSavingChanges"
                                                 :ripple="(productHasChanged && !isSavingChanges && !variantAttributesHasChanged)" type="success" size="large"
                                                 :class="['float-right', 'mt-5']" @click.native="handleSubmit()">
                                        <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                                    </basicButton>

                                </template>

                                <!-- If we are creating -->
                                <template v-if="!isEditing">

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
    import barcodeInput from './components/barcodeInput.vue';
    import isFreeCheckbox from './components/isFreeCheckbox.vue';
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
            skuInput, nameInput, barcodeInput, isFreeCheckbox, salePriceInput, costPriceInput,
            typeSelectInput, descriptionInput, visibilitySwitch, regularPriceInput, stockQuantityInput, locationSelectInput,
            allowVariationsSwitch, Loader, variantAttributes, showDescriptionCheckbox, autoManageStockCheckbox,basicButton,
            maximumQuantityPerOrderInput, allowStockManagementCheckbox, allowMaximumQuantityPerOrderCheckbox, allowMultipleQuantityPerOrderCheckbox
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
                variations: [],
                location_ids: [],
                productForm: null,
                closeDrawer: true,
                isCreating: false,
                localProduct: null,
                productFormRules: {

                },
                isSavingChanges: false,
                isLoadingProduct: false,
                isLoadingLocations: false,
                isLoadingVariations: false,
                isCreatingVariations: false,

                productBeforeChanges: null,
                variantAttributesHasChanged: false,
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
                return this.productForm.is_free;
            },
            noPrice(){
                return !this.isFree && this.productForm.unit_regular_price == 0;
            },
            onSale(){
                return !this.productForm.is_free && this.productForm.unit_sale_price > 0 &&
                       (this.productForm.unit_sale_price < this.productForm.unit_regular_price);
            },
            locationCurrency(){
                return (this.location.currency || {});
            },
            locationCurrencySymbol(){
                return this.locationCurrency.symbol;
            },
            locationCurrencyCode(){
                return this.locationCurrency.code;
            },
            salePercentage(){

                //  Calculate the difference
                var difference = (this.productForm.unit_regular_price - this.productForm.unit_sale_price);

                var percentage = (difference / this.productForm.unit_regular_price) * 100;

                return Math.round(percentage);
            },
            saleSavings(){

                var saleSavings = (this.productForm.unit_regular_price - this.productForm.unit_sale_price);

                //  The formatPrice() method is defined from the miscMixin
                return this.formatPrice(saleSavings, this.locationCurrencySymbol);

            },
            profit(){

                if( this.productForm.unit_sale_price === 0 ){
                    var unitPrice = this.productForm.unit_regular_price;
                }else{
                    var unitPrice = this.productForm.unit_sale_price;
                }

                var unitCost = this.productForm.unit_cost;

                var profit = unitPrice - unitCost;

                if( profit < 0 ) profit = 0;

                //  The formatPrice() method is defined from the miscMixin
                return this.formatPrice(profit, this.locationCurrencySymbol);

            },
            quantityDescription(){

                if( this.productForm.allow_multiple_quantity_per_order == false ){

                    return 'Allow only 1 quantity per order';

                }else if( this.productForm.allow_multiple_quantity_per_order && this.productForm.allow_maximum_quantity_per_order == false ){

                    return 'Allow unlimited quantity per order';

                }else if( this.productForm.allow_multiple_quantity_per_order && this.productForm.allow_maximum_quantity_per_order == true ){

                    return 'Allow only a maximum of '+this.productForm.maximum_quantity_per_order+' quantities per order';

                }
            },
            allowStockManagement(){
                return this.productForm.allow_stock_management;
            },
            stockQuantity(){
                return this.productForm.stock_quantity;
            },
            stockStatus(){

                if( this.allowStockManagement == false ){

                    return {
                        text: 'Unlimited stock',
                        type: 'success'
                    }

                }else if( this.allowStockManagement && this.stockQuantity > 0 ){

                    return {
                        text: 'Limited stock',
                        type: 'success'
                    }

                }else{

                    return {
                        text: 'No stock!',
                        type: 'warning'
                    }

                }
            },
        },
        methods: {

            /** Note the use of "async" and "await". This helps us to perform the
             *  api call and wait for the response before we continue any futher
             */
            async prepareProduct(){

                if( this.isEditing ){

                    //  Reset tthe product form
                    this.productForm = null;

                    //  Fetch the product
                    await this.fetchProduct();

                    //  Notify parent of fetched product
                    this.$emit('fetchedProduct', this.localProduct);

                    //  If this product is not a variation
                    if( !this.isVariationProduct ){

                        //  Fetch the product locations
                        await this.fetchProductLocations();

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

                            //  Get the product
                            self.localProduct = data || null;

                            //  Stop loader
                            self.isLoadingProduct = false;

                        })
                        .catch(response => {

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

                            //  Stop loader
                            this.isLoadingLocations = false;

                        });

                }

            },
            getProductForm(){

                //  Clone the product Object (if any) as a new Object
                var form = _.cloneDeep(Object.assign({},
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
                        is_free: false,
                        currency: this.locationCurrencyCode,
                        unit_regular_price: 0,
                        unit_sale_price : 0,
                        unit_cost: 0,

                        //  Quantity Management
                        allow_multiple_quantity_per_order: true,
                        allow_maximum_quantity_per_order : false,
                        maximum_quantity_per_order: 5,

                        //  Stock Management
                        allow_stock_management: false,
                        auto_manage_stock: true,
                        stock_quantity: 10,

                    //  Overide the default form details with the provided product Object
                    }, this.localProduct));

                if( this.localProduct ){

                    form.show_description = this.localProduct.show_description.status;
                    form.visible = this.localProduct.visible.status;

                    form.allow_variants = this.localProduct.allow_variants.status;

                    form.is_free = this.localProduct.is_free.status;
                    form.currency = this.localProduct.currency.code;
                    form.unit_regular_price = this.localProduct.unit_regular_price.amount;
                    form.unit_sale_price = this.localProduct.unit_sale_price.amount;
                    form.unit_cost = this.localProduct.unit_cost.amount;

                    form.allow_multiple_quantity_per_order = this.localProduct.allow_multiple_quantity_per_order.status;
                    form.allow_maximum_quantity_per_order = this.localProduct.allow_maximum_quantity_per_order.status;

                    form.allow_stock_management = this.localProduct.allow_stock_management.status;
                    form.auto_manage_stock = this.localProduct.auto_manage_stock.status;
                    form.stock_quantity = this.localProduct.stock_quantity.value;

                }

                return form;

            },
            copyProductBeforeUpdate(){

                //  Clone the product before any changes occur
                this.productBeforeChanges = _.cloneDeep( this.productForm );

            },
            restoreProductForm(){

                //  Restore the product form to its original state
                this.productForm = _.cloneDeep(this.productBeforeChanges);

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
            async handleGeneratedVariations(variantAttributes){

                this.$set(this.productForm, 'variant_attributes', variantAttributes);

                if( this.productHasChanged ){

                    //  Make sure the drawer is not closed while handling submit
                    this.closeDrawer = false;

                    //  Save changes
                    await this.handleSubmit();

                    //  Reset the close drawer to its normal state
                    this.closeDrawer = true;

                }

                this.prepareProduct();


            },
            async handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                /**
                 *  Declare and return a promise. This is so that we wait on the saveProduct() or createProduct()
                 *  API Calls when they are triggered, so that we wait for the call to be successful before
                 *  we run other methods that follow soon after the handleSubmit()
                 */
                var promise = await new Promise(resolve => {

                    //  Validate the product form
                    this.$refs['productForm'].validate((valid) =>
                    {
                        //  If the validation passed
                        if (valid) {

                            //  If we are editing
                            if( this.isEditing ){

                                //  Make a promise to save the product
                                resolve(new Promise(resolve => {

                                    //  Resolve the promise by Attempting to save product
                                    resolve( this.saveProduct() );

                                }));

                            }else{

                                //  Make a promise to create the product
                                resolve(new Promise(resolve => {

                                    //  Resolve the promise by Attempting to create product
                                    resolve( this.createProduct() );

                                }));

                            }

                        //  If the validation failed
                        } else {

                            this.$Message.warning({
                                content: 'Sorry, you cannot update product',
                                duration: 6
                            });

                            //  Resolve the promise
                            resolve();
                        }

                    });
                });

                //  If we should close the drawer
                if( this.closeDrawer ){

                    //  Notify the parent
                    this.$emit('closeDrawer');

                }

                return promise;
            },
            async saveProduct() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                /** Make an Api call to create the product. We include the
                 *  product details required for a new product creation.
                 */
                let data = {
                    postData: this.productForm,
                };

                return await api.call('put', this.productUrl, data, this)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Product updated success message
                        self.$Message.success({
                            content: 'Your product has been updated!',
                            duration: 6
                        });

                        self.copyProductBeforeUpdate();

                        //  Notify parent on changes
                        self.$emit('savedProduct', data);

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot update product',
                            duration: 6
                        });

                        //  Don't close the drawer
                        this.closeDrawer = false;

                        //  Stop loader
                        self.isSavingChanges = false;

                });
            },
            createProduct() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isCreating = true;

                /** Make an Api call to create the product. We include the
                 *  product details required for a new product creation.
                 */
                let data = {
                        postData: this.productForm
                    };

                return api.call('post', this.createProductUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent of the product created
                        self.$emit('createdProduct', data);

                        //  Product created success message
                        self.$Message.success({
                            content: 'Your product has been created!',
                            duration: 6
                        });

                        //  resetForm() declared in miscMixin
                        self.resetForm('productForm');

                    }).catch((response) => {

                        this.$Message.warning({
                            content: 'Sorry, you cannot create product',
                            duration: 6
                        });

                        //  Don't close the drawer
                        this.closeDrawer = false;

                        //  Stop loader
                        self.isCreating = false;

                });
            },
        },
        created(){

            //  Prepare the product
            this.prepareProduct();

        }
    };

</script>
