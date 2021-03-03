<template>

    <Row :gutter="12" class="mt-5">

        <Col :span="20" :offset="2">

            <!-- If we are loading, Show Loader -->
            <Loader v-show="isLoading" :divStyles="{ textAlign: 'center' }">Loading products...</Loader>

            <template v-if="!isLoading">

                <div class="clearfix mb-2">

                    <!-- Save Changes Button -->
                    <basicButton v-if="products.length && productsHaveChanged" type="success" size="default"
                                class="float-right ml-2" :ripple="productsHaveChanged"
                                @click.native="updateProductArrangement()">
                        <span>Save Changes</span>
                    </basicButton>

                    <!-- Add Product Button -->
                    <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true"
                                class="float-right" :ripple="!productsExist"
                                @click.native="handleAddProduct()">
                        <span>Add Product</span>
                    </basicButton>

                </div>

                <!-- Product List & Dragger  -->
                <draggable
                    :list="products"
                    @start="drag=true"
                    @end="drag=false"
                    :options="{
                        group:'products',
                        handle:'.dragger-handle',
                        draggable:'.single-draggable-item',
                    }">

                    <!-- Single Product  -->
                    <single-product v-for="(product, index) in products" :key="index" name="single-product"
                        @savedProduct="handleSavedProduct"
                        :location="location"
                        :products="products"
                        :product="product"
                        :store="store"
                        :index="index">
                    </single-product>

                    <!-- No products message -->
                    <Alert v-if="!productsExist" type="info" show-icon>
                        No Products Found
                        <template slot="desc">
                        This location does not have any products yet, start adding products!
                        </template>
                    </Alert>

                </draggable>

            </template>

        </Col>

        <!--
            MODAL TO ADD / CLONE / EDIT PRODUCT
        -->
        <template v-if="isOpenManageProductModal">

            <manageProductDrawer
                :store="store"
                :isCloning="false"
                :isEditing="false"
                :location="location"
                :products="products"
                @createdProduct="handleCreatedProduct($event)"
                @visibility="isOpenManageProductModal = $event">
            </manageProductDrawer>

        </template>

    </Row>

</template>

<script>

    import draggable from 'vuedraggable';
    import manageProductDrawer from './single-product/manageProductDrawer.vue';
    import Loader from './../../../../../../components/_common/loaders/default.vue';
    import basicButton from './../../../../../../components/_common/buttons/basicButton.vue';

    export default {
        components: {
            draggable, manageProductDrawer, Loader, basicButton
        },
        props: {
            store: {
                type: Object,
                default: null
            }
        },
        data(){
            return {
                isOpenManageProductModal: false,
                productsBeforeChanges: null,
                isLoading: false,
                location: null,
                products: []
            }
        },
        computed: {
            totalProduct(){
                return this.products.length;
            },
            productsExist(){
                return this.totalProduct ? true : false;
            },
            addButtonType(){
                return this.productsExist ? 'default' : 'success';
            },
            locationUrl(){
                return decodeURIComponent(this.$route.params.location_url);
            },
            productsHaveChanged(){

                //  Check if the product has been modified
                var status = !_.isEqual(this.products, this.productsBeforeChanges);

                return status;

            },
            productArrangementUrl(){
                return this.location['_links']['bos:product_arrangement'].href;
            }
        },
        methods: {
            handleSavedProduct(product, index){

                //  Check if the state of the products has already been changed
                var hasAlreadyChanged = this.productsHaveChanged;

                //  Update the product
                this.$set(this.products, index, product);

                //  If the state of the products has not already been changed
                if( hasAlreadyChanged == false ){

                    //  Turn off any changes detected
                    this.copyProductsBeforeUpdate();

                }

            },
            handleAddProduct(){
                this.isOpenManageProductModal = true;
            },
            copyProductsBeforeUpdate(){

                //  Copy products before changes
                this.productsBeforeChanges = _.cloneDeep( this.products );

            },
            handleCreatedProduct(product){

                //  Add the new created product to the top of the list
                this.products.unshift(product);

            },
            fetchLocation() {

                //  If we have the location url
                if( this.locationUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    //  Use the api call() function, refer to api.js
                    return api.call('get', this.locationUrl)
                        .then(({data}) => {

                            //  Get the location
                            self.location = data || null;

                            //  Stop loader
                            self.isLoading = false;

                            self.$emit('selectedLocation', self.location)

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoading = false;

                        });
                }

            },
            fetchProducts() {

                //  If we have the location
                if( this.location ){

                    //  Get the location products
                    var productUrl = this.location['_links']['bos:products'].href;

                }else{

                    //  Get the store products
                    var productUrl = this.store['_links']['bos:products'].href;

                }

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                //  Use the api call() function, refer to api.js
                api.call('get', productUrl)
                    .then(({data}) => {

                        //  Get the products
                        self.products = data['_embedded']['products'] || [];

                        self.copyProductsBeforeUpdate();

                        //  Stop loader
                        self.isLoading = false;

                    })
                    .catch(response => {

                        //  Stop loader
                        self.isLoading = false;

                    });

            },
            updateProductArrangement() {

                //  If we have the product arrangement url
                if( this.productArrangementUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    let data = {
                            postData: {
                                location_id: this.location.id,
                                product_arrangements: self.products.map((product, index) => {
                                    return {
                                        "id": product.id,
                                        "arrangement": (index + 1)
                                    };
                                })
                            }
                        };

                    //  Use the api call() function, refer to api.js
                    return api.call('put', this.productArrangementUrl, data)
                        .then(({data}) => {

                            self.copyProductsBeforeUpdate();

                            //  Stop loader
                            self.isLoading = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoading = false;

                        });
                }

            }
        },
        created(){

            //  If the location exists
            if( this.locationUrl ){

                //  Fetch the location
                this.fetchLocation().then(() => {

                    //  After getting the location get the products
                    this.fetchProducts();

                });

            }else{

                //  Get the store products
                this.fetchProducts();

            }

        }
    };

</script>
