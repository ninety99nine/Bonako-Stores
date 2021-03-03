<template>

    <div>

        <!-- If viewing single product -->
        <template v-if="product && isViewingProduct">

            <!-- Single Product -->
            <singleProduct :store="store" :location="location" :assignedLocations="assignedLocations"
                           :products="products" :product="product" @close="handleCloseProduct()">
            </singleProduct>

        </template>

        <!-- If viewing a list of products -->
        <Row v-else class="mt-4">

            <Col :span="22" :offset="1">

                <!-- If viewing products -->
                <template>

                    <!-- Heading, Add Product Button & Watch Video Button -->
                    <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                        <Col :span="12">

                            <!-- Heading -->
                            <h1 :class="['font-weight-bold', 'text-muted']">Products</h1>

                        </Col>

                        <Col :span="12">

                            <div class="clearfix">

                                <!-- Add Product Button -->
                                <basicButton :type="addButtonType" size="default" icon="ios-add" :showIcon="true"
                                            :ripple="!productsExist && !isLoading" :class="['float-right', 'ml-2']"
                                            :disabled="(productsExist && productsHaveChanged) || isLoading"
                                            @click.native="handleAddProduct()">
                                    <span>Add Product</span>
                                </basicButton>

                                <!-- Watch Video Button -->
                                <Button type="primary" size="default" @click.native="fetchProducts()" :class="['float-right']">
                                    <Icon type="ios-play-outline" class="mr-1" :size="20" />
                                    <span>Watch Video</span>
                                </Button>

                            </div>

                        </Col>

                    </Row>

                    <!-- Search Bar, Filters, Arrange Products Switch, Save Changes Button & Refresh Button -->
                    <Row :gutter="12" class="mb-4">

                        <Col :span="8">

                            <!-- Search Bar -->
                            <Input v-model="searchWord" type="text" size="default" :disabled="arrangeProducts" clearable placeholder="Search product..." icon="ios-search-outline"></Input>

                        </Col>

                        <Col :span="8">

                            <!-- Filters -->
                            <Poptip trigger="hover" content="Add filters for specific products" word-wrap class="poptip-w-100">
                                <Select v-model="selectedFilters" size="default" multiple class="w-100"
                                        prefix="ios-funnel-outline" clearable placeholder="Add filters"
                                        :disabled="arrangeProducts" @on-select="fetchProducts()">

                                    <!-- Filter Options-->
                                    <Option v-for="(status, index) in statuses" :value="status.name" :key="index" :label="status.name">
                                        <span :class="['font-weight-bold']">{{ status.name }}</span>
                                        <span v-if="status.desc" style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">{{ status.desc }}</span>
                                    </Option>

                                </Select>
                            </Poptip>

                        </Col>

                        <Col :span="8" :class="['clearfix']">

                            <!-- Arrange Products Switch -->
                            <div v-if="!productsHaveChanged" :class="['float-left', 'mt-1', 'ml-3']">
                                <span :style="{ width: '200px' }" class="font-weight-bold">Arrange Products: </span>
                                <Poptip trigger="hover" word-wrap width="300" content="Turn on to drag and drop and change the arrangement of products">
                                    <i-Switch v-model="arrangeProducts" :disabled="!products.length || isLoading || productsHaveChanged" />
                                </Poptip>
                            </div>

                            <template v-if="products.length && productsHaveChanged">

                                <!-- Save Changes Button -->
                                <basicButton type="success" size="default"
                                            :class="['float-right', 'ml-2']" :ripple="productsHaveChanged && !isLoading"
                                            :disabled="isLoading" :loading="isLoading"
                                            @click.native="updateProductArrangement()">
                                    <span>Save Changes</span>
                                </basicButton>

                                <Button v-if="productsHaveChanged" type="default"
                                        size="default" :class="['float-right']"
                                        @click.native="resetProducts()"
                                        :disabled="isLoading">
                                    <span>Cancel</span>
                                </Button>

                            </template>

                            <!-- Refresh Button -->
                            <Button v-else type="default" size="default" :class="['float-right']"
                                :loading="isLoading" :disabled="isLoading"
                                @click.native="fetchProducts()">
                                <Icon v-show="!isLoading" type="ios-refresh" class="mr-1" :size="20" />
                                <span>Refresh</span>
                            </Button>

                        </Col>

                    </Row>

                    <!-- Product Table -->
                    <Table v-show="!arrangeProducts" class="product-table" :columns="dynamicColumns" :data="products" :loading="isLoading"
                            no-data-text="No products found" :style="{ overflow: 'visible' }">

                        <!-- Price Poptip -->
                        <pricingPoptip slot-scope="{ row, index }" slot="price" :product="row"></pricingPoptip>

                        <!-- Sale Poptip -->
                        <salePoptip slot-scope="{ row, index }" slot="sale" :product="row"></salePoptip>

                        <!-- Price Poptip -->
                        <stockPoptip slot-scope="{ row, index }" slot="stock" :product="row"></stockPoptip>

                        <template slot-scope="{ row, index }" slot="action">

                            <div>
                                <Dropdown trigger="click" placement="bottom-end">
                                    <Icon type="md-more" size="20" :class="['border', 'rounded-circle', 'border-secondary', 'text-secondary']" />
                                    <DropdownMenu slot="list">
                                        <DropdownItem name="View" @click.native="handleViewProduct(row, index)">View</DropdownItem>
                                        <DropdownItem name="Edit" @click.native="handleEditProduct(row, index)">Edit</DropdownItem>
                                        <DropdownItem name="Delete" class="text-danger" @click.native="handleDeleteProduct(row, index)">Delete</DropdownItem>
                                    </DropdownMenu>
                                </Dropdown>
                            </div>

                        </template>

                    </Table>

                    <!-- Draggable Product Cards (Arrange Products) -->
                    <draggable v-show="arrangeProducts" v-model="products" draggable=".draggable-product">

                        <Card v-for="(product, index) in products" :key="index" :class="['draggable-product', 'cursor-pointer', 'mb-1']">

                            <div :class="['clearfix']">

                                <!-- Product Name  -->
                                <span :class="['float-left', 'font-weight-bold']">{{ product.name }}</span>

                                <!-- Move Product Button  -->
                                <Icon :class="['float-right', 'dragger-handle']" type="ios-move" size="20" />

                                <!-- Product Position  -->
                                <span :class="['float-right', 'border-bottom', 'mr-5']"># {{ (index + 1) }}</span>

                            </div>

                        </Card>

                    </draggable>

                </template>

                <!--
                    MODAL TO CREATE / EDIT PRODUCT
                -->
                <template v-if="isOpenManageProductModal">

                    <manageProductDrawer
                        :index="index"
                        :store="store"
                        :product="product"
                        :location="location"
                        :layoutSize="layoutSize"
                        :assignedLocations="assignedLocations"
                        @savedProduct="handleSavedProduct($event)"
                        @createdProduct="handleCreatedProduct($event)"
                        @visibility="isOpenManageProductModal = $event">
                    </manageProductDrawer>

                </template>

                <!--
                    MODAL TO DELETE PRODUCT
                -->
                <template v-if="isOpenDeleteProductModal">

                    <deleteProductModal
                        :index="index"
                        :product="product"
                        :products="products"
                        @deleted="handleDeletedProduct($event)"
                        @visibility="isOpenDeleteProductModal = $event">
                    </deleteProductModal>

                </template>

            </Col>

        </Row>

    </div>

</template>

<script>

    import draggable from 'vuedraggable';
    import singleProduct from './../show/main.vue';
    import statusTag from './../show/components/statusTag.vue';
    import salePoptip from './../show/components/salePoptip.vue';
    import stockPoptip from './../show/components/stockPoptip.vue';
    import pricingPoptip from './../show/components/pricingPoptip.vue';
    import deleteProductModal from './../components/deleteProductModal.vue';
    import manageProductDrawer from './../components/manageProductDrawer.vue';
    import miscMixin from './../../../../../components/_mixins/misc/main.vue';
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';

    export default {
        mixins: [ miscMixin ],
        components: {
            draggable, singleProduct, statusTag, salePoptip, stockPoptip, pricingPoptip, deleteProductModal, 
            manageProductDrawer, basicButton
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
        },
        data(){
            return {
                isOpenDeleteProductModal: false,
                isOpenManageProductModal: false,
                productsBeforeChanges: null,
                arrangeProducts: false,
                isViewingProduct: false,
                layoutSize: null,
                isLoading: false,
                product: null,
                products: [],
                index: null,
                tableColumnsToShowByDefault: [
                    'Selector', 'Name', 'Stock', 'Sale', 'Price', 'Visibility Status', 'Created Date'
                ],
                statuses: [
                    {
                        name: 'Active',
                        desc: 'Products that are available'
                    },
                    {
                        name: 'Inactive',
                        desc: 'Products that are not available'
                    },
                    {
                        name: 'Visible',
                        desc: 'Products that are available and visible'
                    },
                    {
                        name: 'Invisible',
                        desc: 'Products that are available but not visible'
                    },
                    {
                        name: 'Low Stock',
                        desc: 'Products that are low or out of stock'
                    },
                    {
                        name: 'No Price',
                        desc: 'Products that do not have a price'
                    }
                ],
                selectedFilters: [],
                searchTimeout: null,
                searchWord: '',
            }
        },
        computed: {
            totalProducts(){
                return this.products.length;
            },
            productsExist(){
                return this.totalProducts ? true : false;
            },
            addButtonType(){
                return this.productsExist ? 'default' : 'success';
            },
            productsHaveChanged(){

                //  Check if the product has been modified
                return !_.isEqual(this.products, this.productsBeforeChanges);

            },
            productsUrl(){
                return this.location['_links']['bos:products'].href;
            },
            productArrangementUrl(){
                return this.location['_links']['bos:product_arrangement'].href;
            },
            dynamicColumns(){

                var allowedColumns = [];

                //  Product Selector
                if(this.tableColumnsToShowByDefault.includes('Selector')){
                    allowedColumns.push({
                        type: 'selection',
                        align: 'center',
                        width: 60
                    });
                }

                //  Name
                if(this.tableColumnsToShowByDefault.includes('Name')){
                    allowedColumns.push(
                    {
                        title: 'Name',
                        sortable: true,
                        render: (h, params) => {

                            var sku = (params.row.sku || 'N/A');
                            var name = (params.row.name || 'N/A');
                            var barcode = (params.row.barcode || 'N/A');
                            var description = (params.row.description || 'N/A');

                            return h('Poptip', {
                                style: {
                                    width: '100%',
                                    textAlign:'left'
                                },
                                props: {
                                    width: 280,
                                    wordWrap: true,
                                    trigger:'hover',
                                    placement: 'top-start',
                                    title: 'Product Info'
                                }
                            }, [
                                h('span', name),
                                h('List', {
                                        slot: 'content',
                                        props: {
                                            slot: 'content',
                                            size: 'small'
                                        }
                                    }, [
                                        h('ListItem', 'Description: '+description ),
                                        h('ListItem', 'Barcode: '+barcode ),
                                        h('ListItem', 'SKU: '+sku ),
                                    ])
                            ])
                        }
                    });
                }

                //  Visibility Status
                if(this.tableColumnsToShowByDefault.includes('Visibility Status')){
                    allowedColumns.push(
                    {
                        title: 'Visible',
                        render: (h, params) => {
                            //  Visibility Status Badge
                            return h(statusTag, {
                                props: {
                                    type: 'Badge',
                                    visible: params.row.visible
                                }
                            })
                        }
                    })
                }

                //  Price
                if(this.tableColumnsToShowByDefault.includes('Price')){
                    allowedColumns.push(
                        {
                            title: 'Price',
                            slot: 'price',
                            width: 100,
                        }
                    );
                }

                //  Sale
                if(this.tableColumnsToShowByDefault.includes('Sale')){
                    allowedColumns.push(
                        {
                            title: 'Sale',
                            slot: 'sale',
                            width: 100
                        }
                    );
                }

                //  Stock
                if(this.tableColumnsToShowByDefault.includes('Stock')){
                    allowedColumns.push(
                        {
                            title: 'Stock',
                            slot: 'stock',
                            width: 150,
                        }
                    );
                }

                //  Created Date
                if(this.tableColumnsToShowByDefault.includes('Created Date')){
                    allowedColumns.push(
                    {
                        title: 'Date',
                        sortable: true,
                        render: (h, params) => {
                            return h('Poptip', {
                                style: {
                                    width: '100%',
                                    textAlign:'left'
                                },
                                props: {
                                    width: 280,
                                    wordWrap: true,
                                    trigger:'hover',
                                    placement: 'top',
                                    content: 'Date: '+ this.formatDateTime(params.row.created_at.date, true)
                                }
                            }, [
                                h('span', this.formatDateTime(params.row.created_at.date))
                            ])
                        }
                    })
                }

                //  Action
                allowedColumns.push(
                    {
                        title: 'Action',
                        slot: 'action',
                        width: 80,
                    }
                );

                return allowedColumns;
            },
        },
        methods: {
            resetProducts(){
                this.products = _.cloneDeep(this.productsBeforeChanges);
            },
            handleAddProduct(){
                this.index = null;
                this.product = null;
                this.layoutSize = 'small';
                this.handleOpenManageProductModal();
            },
            handleEditProduct(product, index){
                this.index = index;
                this.product = product;
                this.layoutSize = 'small';
                this.handleOpenManageProductModal();
            },
            handleViewProduct(product, index){
                this.index = index;
                this.product = product;
                this.layoutSize = 'large';
                this.isViewingProduct = true;
            },
            handleDeleteProduct(product, index){
                this.index = index;
                this.product = product;
                this.handleOpenDeleteStoreModal();
            },
            handleOpenManageProductModal(){
                this.isOpenManageProductModal = true;
            },
            handleOpenDeleteStoreModal(){
                this.isOpenDeleteProductModal = true;
            },
            handleCloseProduct(){
                this.isViewingProduct = false;
            },
            handleCreatedProduct(product){

                //  Add the new created product to the top of the list
                this.products.unshift(product);

                this.copyProductsBeforeUpdate();

            },
            handleDeletedProduct(){

                this.fetchProducts();

            },
            handleSavedProduct(product){

                //  Update the product
                this.$set(this.products, this.index, product);

                this.copyProductsBeforeUpdate();

            },
            copyProductsBeforeUpdate(){

                //  Copy products before changes
                this.productsBeforeChanges = _.cloneDeep( this.products );

            },
            fetchProducts() {

                //  If we have the products url
                if( this.productsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.productsUrl)
                        .then(({data}) => {

                            //  Get the products
                            self.products = data['_embedded']['products'] || [];

                            //  Turn off any changes detected
                            self.copyProductsBeforeUpdate();

                            //  Stop loader
                            self.isLoading = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoading = false;

                        });
                }

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
                    return api.call('post', this.productArrangementUrl, data)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoading = false;

                            //  Swith to table view
                            self.arrangeProducts = false;

                            //  Turn off any changes detected
                            self.copyProductsBeforeUpdate();

                            self.$Message.success({
                                content: 'Products updated!',
                                duration: 6
                            });

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoading = false;

                        });
                }

            }
        },
        created(){

            //  Get the location products
            this.fetchProducts();

        }
    };

</script>
