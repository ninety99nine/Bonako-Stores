<template>

    <div>

        <!-- Server Error Message Alert -->
        <Alert v-if="serverGeneralError  && !isSavingChanges" type="warning">{{ serverGeneralError }}</Alert>
        <Alert v-if="serverErrorMessage && !isSavingChanges" type="warning">{{ serverErrorMessage }}</Alert>

        <Form ref="productForm" class="product-form" :model="productForm" :rules="productFormRules">

            <!-- Set Active Status -->
            <FormItem prop="active" class="clearfix mb-0">
                <div class="d-flex float-right">
                    <span class="d-block font-weight-bold mr-2">{{ statusText }}: </span>
                    <Poptip trigger="hover" title="Turn On/Off" placement="bottom-end" word-wrap width="300"
                            content="Turn on to allow subscribers to access this product">
                        <i-Switch v-model="productForm.active" :disabled="isCreating || isSavingChanges"/>
                    </Poptip>
                </div>
            </FormItem>

            <!-- Enter Name -->
            <FormItem label="Name" prop="name" :error="serverNameError" class="mb-3">
                <Poptip trigger="focus" placement="top" word-wrap
                        content="Give your product/service a name e.g Microwave">
                    <Input type="text" v-model="productForm.name" placeholder="Enter product name"
                            :disabled="isCreating || isSavingChanges" maxlength="50" show-word-limit class="w-100">
                    </Input>
                </Poptip>
            </FormItem>

            <!-- Allow Variations -->
            <FormItem prop="allow_variations" class="mb-2">

                <!-- Allow Variations Switch -->
                <div class="d-flex">
                    <span :style="{ width: '150px' }">Allow Variations:</span>
                    <Poptip trigger="hover" width="380" placement="top-start" word-wrap>

                        <template slot="content">
                            <span class="d-block">Add variables if this product/service comes in multiple versions e.g) different sizes, materials or colors. You can change the price and information for each variable</span>

                            <span v-if="!isEditing"
                                    style="margin-top: -15px;"
                                    class="border-top d-block pt-2">
                                <span class="font-weight-bold text-primary">Note: Only available when editing this product after you create it</span>
                            </span>
                        </template>
                        <i-Switch
                            true-color="#13ce66"
                            false-color="#ff4949"
                            class="ml-1" size="large"
                            :value="productForm.allow_variants"
                            :disabled="!isEditing || isSavingChanges"
                            @on-change="productForm.allow_variants = $event"
                            :before-change="handleAllowVariantsBeforeChange">
                            <span slot="open">Yes</span>
                            <span slot="close">No</span>
                        </i-Switch>
                    </Poptip>
                </div>

                <!-- Variations -->
                <template v-if="productForm.allow_variants">

                    <!-- Variant Attributes -->
                    <div class="bg-grey-light border mt-2 p-2">

                        <Col :span="6">
                            <span class="font-weight-bold">Option name</span>
                        </Col>

                        <Col :span="16">
                            <span class="font-weight-bold">Option values</span>
                        </Col>

                        <Row v-for="(attribute, index) in productForm.variant_attributes" :gutter="20" :key="index">

                            <Col :span="6">

                                <!-- Attribute Name -->
                                <FormItem prop="option_name" class="mb-2">
                                    <Poptip trigger="focus" placement="top" word-wrap
                                            content="Provide an attribute name e.g Size or Material">
                                        <Input v-model="productForm.variant_attributes[index].name" type="text" placeholder="e.g Sizes"
                                            class="w-100" maxlength="50">
                                        </Input>
                                    </Poptip>
                                </FormItem>

                            </Col>

                            <Col :span="16">

                                <!-- Attribute Values -->
                                <FormItem prop="option_values" class="mb-2">
                                    <Poptip trigger="focus" placement="top" word-wrap
                                            content="Provide attribute values e.g Small, Medium, Large">
                                        <vue-tags-input
                                            v-model="variantAttributeValue[index]" :tags="variantAttributeTags(attribute.values)" class="w-100"
                                            @tags-changed="updateVariantAttributeOptions($event, index)" placeholder="Add variation options"
                                            @adding-duplicate="handleAddingDuplicate($event)"
                                            avoid-adding-duplicates />
                                    </Poptip>
                                </FormItem>

                            </Col>

                            <Col :span="2" v-if="(productForm.variant_attributes || {}).length > 1">

                                <!-- Remove Variant Button  -->
                                <Poptip confirm
                                        title="Are you sure you want to remove this variant? After removing the variant we will delete all the current variations and create new ones."
                                        ok-text="Yes" cancel-text="No" width="300" placement="left"
                                        @on-ok="removeVariantAttribute(index)">
                                    <Icon type="ios-trash-outline" size="20"/>
                                </Poptip>

                            </Col>


                        </Row>

                        <Row :gutter="12">

                            <Col v-if="!hasInvalidVariants" :span="24">

                                <!-- Alert -->
                                <Alert type="warning">Provide variation names and options</Alert>

                            </Col>

                            <Col :span="8">

                                <!-- Generate Variations Button  -->
                                <Poptip confirm
                                        title="Create new variations?"
                                        ok-text="Yes" cancel-text="No" width="300" placement="top-start"
                                        @on-ok="generateVariations(index)">

                                    <basicButton
                                        :disabled="!hasInvalidVariants || isCreatingVariations"
                                        type="success" size="small"
                                        customClass="mt-3 mb-3"
                                        :ripple="false">
                                        Create Variations
                                    </basicButton>
                                </Poptip>

                            </Col>

                            <Col :span="16" class="clearfix">
                                <basicButton
                                    customClass="mt-3 mb-3" :style="{ width: 'fit-content', position:'relative' }"
                                    @click.native="addVariantAttribute()"
                                    :disabled="isCreatingVariations"
                                    type="default" size="small"
                                    class="float-right"
                                    :ripple="false">
                                    + Add Another Variant
                                </basicButton>
                            </Col>
                        </Row>

                    </div>

                    <!-- Heading -->
                    <Divider orientation="left" class="font-weight-bold">Variations</Divider>

                    <!-- Loading Variations Loader -->
                    <Loader v-if="isLoadingVariations" :loading="true" type="text" class="text-left mt-2 mb-2">Loading variations...</Loader>

                    <!-- Creating Variations Loader -->
                    <Loader v-if="isCreatingVariations" :loading="true" type="text" class="text-left mt-2 mb-2">Creating variations...</Loader>

                    <div v-if="!isLoadingVariations && !isCreatingVariations" class="mt-4">

                        <!-- Single Product Variation  -->
                        <single-product v-for="(variation, index) in variations" :key="index" name="single-product-variation"
                            :products="variations"
                            :product="variation"
                            :location="location"
                            :store="store"
                            :index="index"
                            :mask="false">
                        </single-product>

                    </div>

                </template>

            </FormItem>

            <template v-if="!productForm.allow_variants">

                <!-- Select Type -->
                <FormItem prop="type" class="mb-2">
                    <div class="d-flex">
                        <span class="mr-2">Type: </span>
                        <Select v-model="productForm.type" class="w-100">
                            <Option v-for="(type, index) in productTypes" :value="type.value" :key="index">
                                {{ type.name }}
                            </Option>
                        </Select>
                    </div>
                </FormItem>

                <!-- Enter Description -->
                <FormItem label="Description" prop="description" :error="serverDescriptionError" class="mb-3">
                    <Poptip trigger="focus" placement="top" word-wrap
                            content="Describe your product">
                        <Input type="textarea" v-model="productForm.description" placeholder="Enter product description"
                                :disabled="isCreating || isSavingChanges" maxlength="500" show-word-limit class="w-100">
                        </Input>
                    </Poptip>
                </FormItem>

                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold mt-4">Pricing</Divider>

                <!-- Pricing -->
                <Row :gutter="12">

                    <Col :span="8">

                        <!-- Regular Price -->
                        <FormItem label="Regular Price" prop="unit_regular_price" :error="serverRegularPriceError" class="mb-0">
                            <Poptip trigger="click" width="350" placement="top-start" word-wrap
                                    content="What is your product/service usual price?">
                                <InputNumber v-model="productForm.unit_regular_price" size="small" class="w-100"
                                             placeholder="100">
                                </InputNumber>
                            </Poptip>
                        </FormItem>

                    </Col>

                    <Col :span="8">

                        <!-- Sale Price -->
                        <FormItem label="Sale Price" prop="unit_sale_price" :error="serverSalePriceError" class="mb-0">
                            <Poptip trigger="click" width="350" placement="top-start" word-wrap
                                    content="Is your product/service on sale? Add your sale price">
                                <InputNumber v-model="productForm.unit_sale_price" size="small" class="w-100"
                                             :disabled="!productForm.unit_regular_price" placeholder="80"
                                             :max="maximumSalePrice" :min="0" :step="1">
                                </InputNumber>
                            </Poptip>
                        </FormItem>

                    </Col>

                    <Col :span="8">

                        <!-- Cost Per Item -->
                        <FormItem label="Cost Per Item" prop="unit_cost" :error="serverCostPriceError" class="mb-0">
                            <Poptip trigger="click" width="350" placement="top-end" word-wrap
                                    content="How much does this product/service cost you?">
                                <InputNumber v-model="productForm.unit_cost" size="small" class="w-100"
                                            :disabled="!productForm.unit_regular_price" placeholder="50"
                                            :max="maximumSalePrice" :min="0" :step="1">>
                                </InputNumber>
                            </Poptip>
                        </FormItem>

                    </Col>

                </Row>

                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold mt-4">Inventory</Divider>

                <!-- Stock Management -->
                <Row :gutter="12">

                    <Col :span="12">

                        <!-- Allow Stock Management -->
                        <FormItem label="Allow Stock Management" prop="allow_stock_management" class="mb-3">
                            <Poptip trigger="hover" width="380" placement="top-start" word-wrap
                                    content="Does your product/service have stock or limited items?">
                                <i-Switch
                                    true-color="#13ce66"
                                    false-color="#ff4949"
                                    class="ml-1" size="large"
                                    :disabled="isCreating || isSavingChanges"
                                    :value="productForm.allow_stock_management"
                                    @on-change="productForm.allow_stock_management = $event">
                                    <span slot="open">Yes</span>
                                    <span slot="close">No</span>
                                </i-Switch>
                            </Poptip>
                        </FormItem>

                    </Col>

                    <template v-if="productForm.allow_stock_management">

                        <Col :span="12">

                            <!-- Auto Manage Stock -->
                            <FormItem label="Manage Stock Automatically" prop="auto_manage_stock" class="mb-3">
                                <Poptip trigger="hover" width="350" placement="top-end" word-wrap
                                        content="Allow the system to automatically update the number of stock remaining each time customers order or purchase this product/service? - If this is turned off then you must manually update the product quantity yourself">
                                    <i-Switch
                                        true-color="#13ce66"
                                        false-color="#ff4949"
                                        class="ml-1" size="large"
                                        :value="productForm.auto_manage_stock"
                                        :disabled="isCreating || isSavingChanges"
                                        @on-change="productForm.auto_manage_stock = $event">
                                        <span slot="open">Yes</span>
                                        <span slot="close">No</span>
                                    </i-Switch>
                                </Poptip>
                            </FormItem>

                        </Col>

                        <Col :span="8">

                            <!-- Stock Quantity -->
                            <FormItem label="Stock Quantity" prop="stock_quantity" class="mb-3">
                                <Poptip trigger="focus" width="350" placement="top-start" word-wrap
                                        content="How much stock (Quantity) of this product/service do you have?">
                                    <InputNumber v-model="productForm.stock_quantity" class="w-100" placeholder="100"></InputNumber>
                                </Poptip>
                            </FormItem>

                        </Col>

                        <Col :span="8">

                            <!-- SKU -->
                            <FormItem label="SKU (Stock Keeping Unit)" prop="sku" class="mb-3">
                                <Poptip trigger="focus" width="380" placement="top-start" word-wrap
                                        content="Assign a unique number to this product to identify it for inventory management">
                                    <Input type="text" v-model="productForm.sku" placeholder="Enter unique code" class="w-100"></Input>
                                </Poptip>
                            </FormItem>

                        </Col>

                        <Col :span="8">

                            <!-- Barcode -->
                            <FormItem label="Barcode" prop="barcode" class="mb-3">
                                <Poptip trigger="focus" width="380" placement="top-end" word-wrap
                                        content="Assign a unique barcode to this product to identify it for inventory management">
                                    <Input type="text" v-model="productForm.barcode" placeholder="Enter unique barcode" class="w-100"></Input>
                                </Poptip>
                            </FormItem>

                        </Col>

                    </template>

                </Row>

            </template>

            <template v-if="isEditing">

                <!-- Save Changes Button -->
                <FormItem v-if="!isSavingChanges">

                    <basicButton :disabled="(!productHasChanged || isSavingChanges)" :loading="isSavingChanges"
                                 :ripple="(productHasChanged && !isSavingChanges)" type="success" size="large"
                                 class="float-right mt-3" @click.native="handleSubmit()">
                        <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                    </basicButton>

                </FormItem>

                <!-- If we are loading, Show Loader -->
                <Loader v-show="isSavingChanges" class="mt-2">Saving product...</Loader>

            </template>

            <template v-else>

                <!-- Create Button -->
                <FormItem v-if="!isCreating">

                    <basicButton :disabled="(!productHasChanged || isCreating)" :loading="isCreating"
                                 :ripple="(productHasChanged && !isCreating)" type="success" size="large"
                                 class="float-right" @click.native="handleSubmit()">
                        <span>{{ isCreating ? 'Creating...' : 'Create' }}</span>
                    </basicButton>

                </FormItem>

                <!-- If we are loading, Show Loader -->
                <Loader v-show="isCreating" class="mt-2">Creating product...</Loader>

            </template>

        </Form>

    </div>

</template>
<script>

    import basicButton from './../../../../../../../components/_common/buttons/basicButton.vue';
    import Loader from './../../../../../../../components/_common/loaders/default.vue';
    import miscMixin from './../../../../../../../components/_mixins/misc/main.vue';
    import VueTagsInput from '@johmun/vue-tags-input';

    export default {
        mixins: [miscMixin],
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            product: {
                type: Object,
                default: null
            }
        },
        components: { basicButton, Loader, VueTagsInput },
        data () {

            return {
                variations: [],
                productForm: null,
                isCreating: false,
                isSavingChanges: false,
                productBeforeChanges: null,
                variantAttributeValue: [],
                productFormRules: {
                    name: [
                        { required: true, message: 'Please enter your product name', trigger: 'blur' },
                        { min: 3, message: 'Product name is too short', trigger: 'change' },
                        { max: 50, message: 'Product name is too long', trigger: 'change' }
                    ],
                    description: [
                        { min: 3, message: 'Description is too short', trigger: 'change' },
                        { max: 500, message: 'Description is too long', trigger: 'change' }
                    ],
                    type: [
                        { required: true, message: 'Select product type', trigger: 'change' }
                    ]
                },
                productTypes: [
                    {
                        name: 'Physical',
                        value: 'physical'
                    },
                    {
                        name: 'Service',
                        value: 'service'
                    }
                ],
                isLoadingVariations: false,
                isCreatingVariations: false,
                variantAttributesBeforeChange: null,
            }
        },
        watch: {
            /** Keep track of changes on the product
             *  This could include product changes done
             *  using the parent component
             */
            product: {

                handler: function (val, oldVal) {

                    //  Reset everything
                    this.setProductFormAndCaptureBeforeChanges();

                },
                deep: true

            },
            /** Watch to see if we want to save changes.
             *  If we do handle the request.
             */
            requestToSaveChanges(newVal, oldVal){
                this.handleSubmit();
            }
        },
        computed: {
            serverGeneralError(){
                return (this.serverErrors || {}).general;
            },
            serverNameError(){
                return (this.serverErrors || {}).name;
            },
            serverDescriptionError(){
                return (this.serverErrors || {}).description;
            },
            serverRegularPriceError(){
                return (this.serverErrors || {}).unit_regular_price;
            },
            serverSalePriceError(){
                return (this.serverErrors || {}).unit_sale_price;
            },
            serverCostPriceError(){
                return (this.serverErrors || {}).unit_cost;
            },
            variationsUrl(){
                if( this.product ){
                    return this.product['_links']['bos:variations'].href;
                }
            },
            productUrl(){
                if( this.product ){
                    return this.product['_links']['self'].href;
                }
            },
            createProductUrl(){
                if( this.location ){

                    return this.location['_links']['bos:product-create'].href;

                }else if( this.store ){

                    return this.store['_links']['bos:product-create'].href;

                }
            },
            isEditing(){
                return this.product ? true : false
            },
            hasInvalidVariants(){

                //  Check if we have variant attributes
                if((this.productForm.variant_attributes || {}).length){

                    for(var x=0; x < (this.productForm.variant_attributes || {}).length; x++){

                        //  Get the current variant key e.g size, color, material, e.t.c
                        let attribute_name = this.productForm.variant_attributes[x].name;

                        //  Get the current variant value e.g ["SM", "M", "L"], ["Blue", "Red"] or ["Cotton", "Nylon"]
                        let attribute_values = this.productForm.variant_attributes[x].values;

                        // If the name or options have not been set then this is not valid variant attribute
                        if( !attribute_name || !attribute_values.length ){

                            return false;

                        }
                    }
                }

                return true;
            },
            statusText(){
                return this.productForm.active ? 'Online' : 'Offline';
            },
            maximumSalePrice(){
                var regular_price = this.productForm.unit_regular_price || 0;
                var less_than_regular_price = regular_price - 1;

                return (less_than_regular_price > 0) ? less_than_regular_price : 0;
            },
            productHasChanged(){

                //  Check if the product has been modified
                var status = !_.isEqual(this.productForm, this.productBeforeChanges);

                //  Notify the parent component of the change status
                this.$emit('unsavedChanges', status);

                return status;

            },
        },
        methods: {
            setProductFormAndCaptureBeforeChanges(){

                //  Set the form details
                this.productForm = this.getProductForm();

                //  Save the form before any changes
                this.copyProductBeforeUpdate();

            },
            getProductForm(){

                return _.cloneDeep(Object.assign({},
                    //  Set the default form details
                    {
                        name: '',
                        active: true,
                        description: '',
                        type: 'physical',
                        allow_variants: false,
                        variant_attributes: null,

                        //  Pricing
                        unit_regular_price: 0,
                        unit_sale_price : 0,
                        unit_cost: 0,

                        //  Inventory
                        allow_stock_management: true,
                        auto_manage_stock: true,
                        stock_quantity: 0,
                        barcode: 0,
                        sku : 0,

                    //  Overide the default form details with the provided product details
                    }, this.product));

            },
            copyProductBeforeUpdate(){

                //  Clone the product
                this.productBeforeChanges = _.cloneDeep( this.productForm );

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

                //  Notify parent that this component is saving data
                self.$emit('isSaving', self.isSavingChanges);

                /** Make an Api call to create the product. We include the
                 *  product details required for a new product creation.
                 */
                let data = {
                        postData: this.productForm
                    };

                return api.call('put', this.productUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Notify parent that this component is not saving data
                        self.$emit('isSaving', self.isSavingChanges);

                        self.$emit('savedProduct', data);

                        //  Product updated success message
                        self.$Message.success({
                            content: 'Your product has been updated!',
                            duration: 6
                        });

                        self.copyProductBeforeUpdate();

                    }).catch((response) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Notify parent that this component is not saving data
                        self.$emit('isSaving', self.isSavingChanges);

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
                let data = {
                        postData: this.productForm
                    };

                //  Set the product store details
                data['postData']['store_id'] = (this.store || {}).id

                //  Set the product location details
                data['postData']['location_id'] = (this.location || {}).id

                return api.call('post', this.createProductUrl, data)
                    .then(({data}) => {

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

                        //  resetForm() declared in miscMixin
                        self.resetForm('productForm');

                    }).catch((response) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent that this component is not creating
                        self.$emit('isCreating', self.isCreating);

                });
            },
            fetchVariations() {

                //  If we have the product url
                if( this.variationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingVariations = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.variationsUrl)
                        .then(({data}) => {

                            //  Get the product
                            self.variations = data['_embedded']['products'] || [];

                            //  Stop loader
                            self.isLoadingVariations = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingVariations = false;

                        });
                }
            },
            storeOriginalVariantAttributesData(){

                //  Store the original product variant attributes
                this.variantAttributesBeforeChange = _.cloneDeep(this.productForm.variant_attributes);

            },
            handleAllowVariantsBeforeChange () {
                return new Promise((resolve) => {

                    //  If the switch was on but is being turned off
                    if(this.productForm.allow_variants){

                        //  Restore the variant attributes to their original state
                        this.productForm.variant_attributes = this.variantAttributesBeforeChange;


                    //  If the switch was off but is being turned on
                    }else{

                        //  Fetch the product variations
                        this.fetchVariations();

                        //  Store the original product variant attributes
                        this.storeOriginalVariantAttributesData();

                        //  If the product does not already have variant attributes
                        if( !(this.variantAttributesBeforeChange || []).length ){

                            //  Add the default variable attributes
                            this.productForm.variant_attributes = _.cloneDeep(this.defaultVariantAttributes);

                        }

                    }

                    //  This concludes our promise
                    resolve();

                });
            },
            variantAttributeTags(variant_attributes){
                return variant_attributes.map(attribute => {
                    return {
                        text: attribute
                    }
                });
            },
            updateVariantAttributeOptions(tags, index){
                 this.productForm.variant_attributes[index].values = tags.map(tag => {
                    return tag.text
                });
            },
            addVariantAttribute(){

                if( this.productForm.variant_attributes == null ){

                    this.productForm.variant_attributes = [];

                }

                this.productForm.variant_attributes.push({  name: 'Color', values: ['Blue', 'Red'] });
            },
            removeVariantAttribute(index) {

                //  If we have more that one variant attribute
                if( this.productForm.variant_attributes.length > 1 ){

                    //  Remove the variant attribute
                    this.productForm.variant_attributes.splice(index, 1);

                    /** Update the product details. This is so that we can actually save the current
                     *  variant attributes of the product.
                     */
                    self.handleSubmit();

                    /** Re-fetch the product variations so that they can pick up the changes of the
                     *  parent variant attributes.
                     */
                    self.fetchVariations();

                }else{

                    this.$Notice.warning({

                        title: 'You must have atleast one variant'

                    });

                }

            },
            generateVariations() {

                //  Hold constant reference to the vue instance
                const self = this;

                if( (self.productForm.variant_attributes || []).length ){

                    //  Start loader
                    self.isCreatingVariations = true;

                    let data = {
                            postData: self.productForm.variant_attributes
                        };

                    //  Use the api call() function located in resources/js/api.js
                    api.call('post', this.variationsUrl, data)
                        .then(({data}) => {

                            //  Stop loader
                            self.isCreatingVariations = false;

                            //  Store the product variations data
                            self.variations = ((data || {})._embedded || {}).products || [];

                            /*  Update the rest of the product details. This is because we want a fresh instance
                             *  of this product with the updated attributes. Remember that since we updated
                             *  the variations this will affect specific attributes on the product iteself.
                             *  We therefore need a fresh version to pick up those changed attributes.
                             */
                            self.handleSubmit();

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isCreatingVariations = false;

                        });

                }
            }
        },
        created(){

            this.setProductFormAndCaptureBeforeChanges();

            //  If the product allows variations
            if( (this.productForm || {}).allow_variants ){

                //  Get the product variations
                this.fetchVariations();

            }

        }
    }
</script>
