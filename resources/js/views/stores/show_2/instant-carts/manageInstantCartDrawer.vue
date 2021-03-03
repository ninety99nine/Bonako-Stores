<template>
    <div>
        <!-- Modal

             Note: drawerVisible and detectClose() are imported from the drawerMixin.
             They are used to allow for opening and closing the drawer properly
             during the v-if conditional statement of the parent component. It
             is important to note that <Modal> does not open/close well with
             v-if statements by default, therefore we need to add additional
             functionality to enhance the experience. Refer to drawerMixin.
        -->
        <Drawer
            width="650"
            :okText="okText"
            cancelText="Cancel"
            :title="drawerTitle"
            :maskClosable="false"
            v-model="drawerVisible"
            @on-visible-change="detectClose">

            <Alert v-if="serverErrorMessage && !isCreating && !isSavingChanges" type="warning">{{ serverErrorMessage }}</Alert>

            <Form ref="instantCartForm" class="instant-cart-form" :model="instantCartForm" :rules="instantCartFormRules">

                <!-- Set Active Status -->
                <FormItem prop="active" class="clearfix mb-0">
                    <div class="d-flex float-right">
                        <span class="d-block font-weight-bold mr-2">{{ statusText }}: </span>
                        <Poptip trigger="hover" title="Turn On/Off" placement="bottom-end" word-wrap width="300"
                                content="Turn on to allow subscribers to use this instant cart">
                            <i-Switch v-model="instantCartForm.active" :disabled="isLoadingAnything"/>
                        </Poptip>
                    </div>
                </FormItem>

                <!-- Enter Name -->
                <FormItem label="Name" prop="name" class="mb-3">
                    <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                            content="Give your instant cart a name e.g Express Fries & Wings">
                        <Input type="text" v-model="instantCartForm.name" placeholder="Enter instant cart name"
                                :disabled="isLoadingAnything" maxlength="50" show-word-limit class="w-100">
                        </Input>
                    </Poptip>
                </FormItem>

                <!-- Enter Description -->
                <FormItem label="Description" prop="description" class="mb-3">
                    <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100" content="Describe your instant cart">
                        <Input type="textarea" v-model="instantCartForm.description" placeholder="Enter instant cart description"
                                :disabled="isLoadingAnything" maxlength="500" show-word-limit class="w-100">
                        </Input>
                    </Poptip>
                </FormItem>

                <!-- If we do not have a location -->
                <div class="clearfix">

                    <!-- Allow location selection -->
                    <div class="d-flex mb-2">
                        <span class="mt-1 mr-1">Location: </span>
                        <Poptip trigger="hover" content="Which location does this instant cart belong to?" word-wrap class="poptip-w-100">
                            <Select v-model="instantCartForm.location_id" filterable class="w-100" :disabled="isLoadingAnything"
                                    placeholder="Select location" @on-change="setLocation">
                                <Option v-for="(location, index) in locations" :value="location.id" :key="index">{{ location.name }}</Option>
                            </Select>
                        </Poptip>
                    </div>

                    <div class="clearfix">

                        <!-- Refresh Button -->
                        <Button type="default" size="small" class="float-right mt-2" @click.native="fetchLocations()">
                            <span class="d-flex">
                                <Icon type="ios-refresh" :size="20" class="mr-1"/>
                                <span>Refresh</span>
                            </span>
                        </Button>

                    </div>

                </div>

                <!-- Products -->
                <div class="clearfix">

                    <Divider orientation="left" class="font-weight-bold mt-4">Products</Divider>
                    <div class="d-flex mb-2">
                        <Poptip trigger="hover" content="Which products do you want to add to this instant cart?" word-wrap class="poptip-w-100">
                            <Select v-model="selectedProducts" filterable multiple class="w-100"
                                    :disabled="isLoadingAnything" :key="resetProductSelectorKey">
                                <Option v-for="(product, index) in products" :value="product.id" :key="index"
                                     :label="product.quantity > 0 ? product.quantity+'x('+product.name+')' : (product.name)">
                                    <span>{{ product.name }}</span>
                                    <span :style="{ color:'#ccc' }" class="float-right mr-4">
                                        Qty: {{ product.allow_stock_management ? product.quantity : 'N/A' }}
                                    </span>
                                </Option>
                            </Select>
                        </Poptip>
                    </div>
                    <div class="clearfix">

                        <span v-if="!instantCartForm.location_id" class="font-weight-bold text-info font-italic float-left my-2">Select location first</span>
                        <span v-else-if="!instantCartForm.products.length" class="font-weight-bold text-info font-italic float-left my-2">Select products for this cart</span>

                        <!-- Manage Quantity Button -->
                        <Button v-if="instantCartForm.products.length" type="primary" size="small" class="float-right mt-2"
                                @click.native="isOpenProductQuantityModal = true">
                            <span>Manage Quantity</span>
                        </Button>

                        <!-- Refresh Button -->
                        <Button type="default" size="small" class="float-right mt-2 mr-2" @click.native="fetchProducts()" :disabled="isLoadingProducts">
                            <span class="d-flex">
                                <Icon type="ios-refresh" :size="20" class="mr-1"/>
                                <span>Refresh</span>
                            </span>
                        </Button>

                    </div>

                </div>

                <!-- Coupons -->
                <div class="clearfix">

                    <Divider orientation="left" class="font-weight-bold mt-4">Coupons</Divider>
                    <div class="d-flex mb-2">
                        <Poptip trigger="hover" content="Which coupons do you want to apply to this instant cart?" word-wrap class="poptip-w-100">
                            <Select v-model="instantCartForm.coupon_ids" filterable multiple class="w-100" :disabled="isLoadingAnything">
                                <Option v-for="(coupon, index) in coupons" :value="coupon.id" :key="index">
                                    {{ coupon.name }}
                                </Option>
                            </Select>
                        </Poptip>
                    </div>
                    <div class="clearfix">

                        <span v-if="!instantCartForm.location_id" class="font-weight-bold text-info font-italic float-left my-2">Select location first</span>

                        <!-- Refresh Button -->
                        <Button type="default" size="small" class="float-right mt-2" @click.native="fetchCoupons()" :disabled="isLoadingCoupons">
                            <span class="d-flex">
                                <Icon type="ios-refresh" :size="20" class="mr-1"/>
                                <span>Refresh</span>
                            </span>
                        </Button>

                    </div>

                </div>

                <template v-if="isEditing">

                    <!-- Save Changes Button -->
                    <FormItem v-if="!isSavingChanges">

                        <basicButton :disabled="(!instantCartHasChanged || isSavingChanges || !hasProducts)" :loading="isSavingChanges"
                                    :ripple="(instantCartHasChanged && !isSavingChanges && hasProducts)" type="success" size="large"
                                    class="float-right mt-3" @click.native="handleSubmit()">
                            <span>{{ isSavingChanges ? 'Saving...' : 'Save Changes' }}</span>
                        </basicButton>

                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isSavingChanges" class="mt-2">Saving instant cart...</Loader>

                </template>

                <template v-else>

                    <!-- Create Button -->
                    <FormItem v-if="!isCreating">

                        <basicButton :disabled="(!instantCartHasChanged || isCreating || !hasProducts)" :loading="isCreating"
                                    :ripple="(instantCartHasChanged && !isCreating && hasProducts)" type="success" size="large"
                                    class="float-right mt-3" @click.native="handleSubmit()">
                            <span>{{ isCreating ? 'Creating...' : 'Create Instant Cart' }}</span>
                        </basicButton>

                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isCreating" class="mt-2">Creating product...</Loader>

                </template>


            </Form>

            <!--
                MODAL EDIT PRODUCT QUANTITY
            -->
            <template v-if="isOpenProductQuantityModal">

                <manageProductQuantityModal
                    :instantCart="instantCartForm"
                    @updated="handleUpdatedProductQuantity($event)"
                    @visibility="isOpenProductQuantityModal = $event">
                </manageProductQuantityModal>

            </template>

        </Drawer>

    </div>
</template>
<script>

    import manageProductQuantityModal from './manageProductQuantityModal';
    import Loader from '../../../../components/_common/loaders/default.vue';
    import drawerMixin from '../../../../components/_mixins/drawer/main.vue';
    import basicButton from '../../../../components/_common/buttons/basicButton.vue';

    export default {
        mixins: [ drawerMixin ],
        components: { manageProductQuantityModal, basicButton, Loader },
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            index: {
                type: Number,
                default: null
            },
            instantCart: {
                type: Object,
                default: null
            }
        },
        data(){
            return{
                locations: [],
                isCreating: false,
                isSavingChanges: false,
                instantCartLocation: this.location,
                isOpenProductQuantityModal: false,
                instantCartFormRules: {
                    name: [
                        { required: true, message: 'Enter your instant cart name', trigger: 'blur' },
                        { min: 3, message: 'Instant cart name is too short', trigger: 'change' },
                        { max: 50, message: 'Instant cart name is too long', trigger: 'change' }
                    ],
                    description: [
                        { min: 3, message: 'Description is too short', trigger: 'change' },
                        { max: 500, message: 'Description is too long', trigger: 'change' }
                    ]
                },
                resetProductSelectorKey: 1,
                isLoadingLocations: false,
                isLoadingProducts: false,
                isLoadingCoupons: false,
                formBeforeChanges: null,
                instantCartForm: null,
                products: [],
                coupons: [],
            }
        },
        watch: {

            //  Watch for changes on the locations
            locations: {
                handler: function (newLocations, oldLocations) {

                    //  Foreach location
                    for (let x = 0; x < newLocations.length; x++) {

                        //  Check if it matches the selected instant cart location id
                        if( newLocations[x].id == this.instantCartForm.location_id){

                            //  If it does, then select this location
                            this.instantCartLocation = Object.assign({}, newLocations[x]);

                        }
                    }
                }
            },
            //  Watch for changes on the instant cart location
            instantCartLocation: {
                handler: function (newVal, oldVal) {

                        //  Fetch the products
                        this.fetchProducts();

                        //  Fetch the coupons
                        this.fetchCoupons();

                }
            },
            //  Watch for changes on anything loading
            isLoadingAnything: {
                handler: function (newVal, oldVal) {

                    //  If everything stops loading and we haven't saved the original form yet
                    if(newVal == false && this.formBeforeChanges == null){

                        //  Copy the form before any changes are made
                        this.copyFormBeforeUpdate();

                    }

                }
            }

        },

        computed: {
            isLoadingAnything(){
                return (this.isLoadingLocations || this.isLoadingProducts || this.isLoadingCoupons || this.isCreating || this.isSavingChanges)
            },
            isEditing(){
                return this.instantCart ? true : false
            },
            hasProducts(){
                return ((this.instantCartForm || {}).products || {}).length ? true : false;
            },
            selectedProducts:{
                get(){
                    return (this.instantCartForm || {}).products.map((product) => {
                        return product.id
                    });
                },
                set(selectedProductIds){

                    this.instantCartForm.products = selectedProductIds.map((selectedProductId) => {

                        var filteredProducts = this.products.filter((product) => {
                            return product.id == selectedProductId;
                        });

                        return filteredProducts[0];
                    });

                }
            },
            okText(){
                //  If we have an instant cart then use "Save Changes" otherwise "Create Instant Cart" as the ok text
                return this.instantCart ? 'Save Changes' : 'Create Instant Cart';
            },
            drawerTitle(){
                if( this.instantCart ){
                    return 'Edit Instant Cart';
                }else{
                    return 'Create Instant Cart';
                }
            },
            statusText(){
                return this.instantCartForm.active ? 'Online' : 'Offline';
            },
            instantCartHasChanged(){

                //  Check if the instant cart has been modified
                var status = !_.isEqual(this.instantCartForm, this.formBeforeChanges);

                //  Notify the parent component of the change status
                this.$emit('unsavedChanges', status);

                return status;

            },
            locationsUrl(){

                //  Get the store locations
                return this.store['_links']['bos:locations'].href;

            },
            productsUrl(){

                //  If we have the location
                if( this.instantCartLocation ){

                    //  Get the location products
                    return this.instantCartLocation['_links']['bos:products'].href;

                }else{

                    //  Get the store products
                    return this.store['_links']['bos:products'].href;

                }

            },
            couponsUrl(){
                //  Get the store products
                return this.store['_links']['bos:coupons'].href;
            },
            instantCartUrl(){
                if( this.instantCart ){
                    return this.instantCart['_links']['self'].href;
                }
            },
            createInstantCartUrl(){
                return this.instantCartLocation['_links']['bos:instant-cart-create'].href;
            },
        },
        methods: {
            setForm(){

                this.instantCartForm = _.cloneDeep(Object.assign({},
                    //  Set the default form details
                    {
                        name: '',
                        active: true,
                        description: '',
                        coupon_ids: [],
                        products: [],

                        store_id: this.store.id,
                        location_id: (this.location || {}).id,

                    //  Overide the default form details with the provided instant cart details
                    }, this.instantCart));

                if( this.instantCart ){

                    //  Set the products
                    this.instantCartForm['products'] = this.instantCart['_embedded'].products.map((product) => {

                        product.quantity = product.pivot.quantity;

                        return product;
                    });

                    //  Set the coupon ids
                    this.instantCartForm['coupon_ids'] = this.instantCart['_embedded'].coupons.map((coupon)=>{
                        return coupon.id;
                    });

                }

            },
            copyFormBeforeUpdate(){

                //  Clone the product
                this.formBeforeChanges = _.cloneDeep( this.instantCartForm );

            },
            fetchProducts() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingProducts = true;

                //  Use the api call() function, refer to api.js
                api.call('get', this.productsUrl)
                    .then(({data}) => {

                        //  Get the instant carts
                        self.products = data['_embedded']['products'] || [];

                        //  Add the default quantity to the products
                        self.products = (this.products || []).map((product, index) => {

                            //  Set the product quantity to "1"
                            this.$set(this.products[index], 'quantity', 1);

                            return product;

                        });

                        //  Update the product quantities
                        this.handleUpdatedProductQuantity(this.instantCartForm.products);

                        //  Stop loader
                        self.isLoadingProducts = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isLoadingProducts = false;

                    });

            },
            fetchCoupons() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingCoupons = true;

                //  Use the api call() function, refer to api.js
                api.call('get', this.couponsUrl)
                    .then(({data}) => {

                        //  Get the instant carts
                        self.coupons = data['_embedded']['coupons'] || [];

                        //  Stop loader
                        self.isLoadingCoupons = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isLoadingCoupons = false;

                    });

            },
            fetchLocations() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingLocations = true;

                //  Use the api call() function, refer to api.js
                api.call('get', this.locationsUrl)
                    .then(({data}) => {

                        //  Get the instant carts
                        self.locations = data['_embedded']['locations'] || [];

                        //  Stop loader
                        self.isLoadingLocations = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isLoadingLocations = false;

                    });

            },
            setLocation(locationId){

                if( this.locations.length ){

                    //  Get the selected location
                    var filteredLocations = this.locations.filter( (location) => {
                        return ( location.id == locationId );
                    });

                    if( filteredLocations.length ){

                        this.instantCartLocation = Object.assign({}, filteredLocations[0]);

                    }

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
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the instant cart form
                this.$refs['instantCartForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  If we are editing
                        if( this.isEditing ){

                            //  Attempt to save instant cart
                            this.saveInstantCart();

                        }else{

                            //  Attempt to create instant cart
                            this.createInstantCart();

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
            saveInstantCart() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSavingChanges = true;

                //  Notify parent that this component is saving data
                self.$emit('isSaving', self.isSavingChanges);

                /** Make an Api call to save the instant cart. We include the
                 *  instant cart details required for saving this instant cart
                 */
                let data = {
                        postData: self.instantCartForm
                    };

                return api.call('put', this.instantCartUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Notify parent that this component is not saving data
                        self.$emit('isSaving', self.isSavingChanges);

                        self.$emit('savedInstantCart', data, self.index);

                        //  Instant cart updated success message
                        self.$Message.success({
                            content: 'Your instant cart has been updated!',
                            duration: 6
                        });

                        //  resetForm() declared in miscMixin
                        self.resetForm('instantCartForm');

                        self.copyFormBeforeUpdate();

                        self.closeDrawer();

                    }).catch((response) => {

                        //  Stop loader
                        self.isSavingChanges = false;

                        //  Set the general error message
                        self.serverErrorMessage = (data || {}).message;

                        //  Notify parent that this component is not saving data
                        self.$emit('isSaving', self.isSavingChanges);

                });
            },
            createInstantCart() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                this.isCreating = true;

                //  Notify parent that this component is creating
                this.$emit('isCreating', this.isCreating);

                /** Make an Api call to create the instant cart. We include the
                 *  instant cart details required for a new instant cart
                 *  creation.
                 */
                let data = {
                        postData: self.instantCartForm
                    };

                return api.call('post', this.createInstantCartUrl, data)
                    .then(({data}) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Notify parent that this component is not creating
                        self.$emit('isCreating', self.isCreating);

                        //  Notify parent of the instant cart created
                        self.$emit('createdInstantCart', data);

                        //  Instant cart created success message
                        self.$Message.success({
                            content: 'Your instant cart has been created!',
                            duration: 6
                        });

                        //  resetForm() declared in miscMixin
                        self.resetForm('instantCartForm');

                        self.closeDrawer();

                    }).catch((response) => {

                        //  Stop loader
                        self.isCreating = false;

                        //  Set the general error message
                        self.serverErrorMessage = (data || {}).message;

                        //  Notify parent that this component is not creating
                        self.$emit('isCreating', self.isCreating);

                });
            },
        },
        created(){

            //  Set the form
            this.setForm();

            //  Fetch the locations
            this.fetchLocations();

        }
    }
</script>
