<template>

    <Card :class="cardClasses">
        
        <!-- Product Title -->
        <div slot="title" class="cursor-pointer" @click="toggleExpansion()">
            
            <Row>

                <Col :span="14" class="d-flex">

                    <!-- Expand / Collapse Icon  -->
                    <Icon :type="arrowDirection" 
                          class="text-primary cursor-pointer mr-2" :size="20" 
                          :style="{ marginTop: '-3px' }" @click.stop="toggleExpansion()" />

                    <!-- Product name  -->
                    <span class="single-draggable-item-title d-block font-weight-bold cut-text" v-html="product.name"></span>

                </Col>

                <Col class="d-flex" :span="10">
                
                    <!-- Summary Poptip -->
                    <Poptip trigger="hover" width="350" word-wrap>

                        <List slot="content" size="small">
                            
                            <template v-if="product.allow_variants">

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Variations: </span>
                                    <span v-if="productVariationsExist" class="cut-text">{{ numberOfVariations }} {{ numberOfVariations == 1 ? ' variation' : 'variations' }}</span>
                                    <span v-else class="cut-text text-danger">No variations found</span>
                                </ListItem>

                            </template>

                            <template v-else>

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Type: </span>
                                    <span class="cut-text">{{ product.type }}</span>
                                </ListItem>

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Unit Price: </span>
                                    <span v-if="product.unit_regular_price" class="cut-text">{{ product.unit_regular_price }}</span>
                                    <span v-else class="cut-text text-danger">No price</span>
                                </ListItem>

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Sale Price: </span>
                                    <span v-if="product.unit_sale_price" class="cut-text">{{ product.unit_sale_price }}</span>
                                    <span v-else class="cut-text text-info">No sale price</span>
                                </ListItem>

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Cost: </span>
                                    <span v-if="product.cost_per_item" class="cut-text">{{ product.cost_per_item }}</span>
                                    <span v-else class="cut-text text-info">No cost</span>
                                </ListItem>

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Allow Stock: </span>
                                    <span class="cut-text">{{ product.allow_stock_management ? 'Yes' : 'No' }}</span>
                                </ListItem>

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Auto Manage Stock: </span>
                                    <span class="cut-text">{{ product.auto_manage_stock ? 'Yes' : 'No' }}</span>
                                </ListItem>

                                <ListItem class="p-0">
                                    <span class="font-weight-bold mr-1">Quantity: </span>
                                    <span v-if="product.allow_stock_management && product.stock_quantity" class="cut-text">{{ product.stock_quantity }}</span>
                                    <span v-else class="cut-text text-danger">No quantity</span>
                                </ListItem>
                                
                                <ListItem v-if="product.description" class="list-item-comment">
                                    <span>
                                        <Icon type="ios-chatbubbles-outline" :size="20" class="mr-1" />
                                        <span class="font-weight-bold">Description: </span><br>{{ product.description }}
                                    </span>
                                </ListItem>
                                
                            </template>
                        
                        </List>

                        <Icon v-if="isValidProduct" type="ios-information-circle-outline" class="text-primary mr-1" :style="{ marginTop: '-5px' }" size="30" />
                        <Icon v-else type="ios-alert-outline" class="text-danger mr-1" :style="{ marginTop: '-5px' }" size="30" />

                    </Poptip>

                    <!-- Active Status -->
                    <div :style="{ marginTop: '-4px' }">
                        <Tag v-if="product.allow_variants" type="border" color="cyan" class="font-weight-bold">
                            {{ numberOfVariations }} {{ numberOfVariations == 1 ? ' variation' : 'variations' }}
                        </Tag>
                    </div>

                </Col>

            </Row>
            
        </div>

        <!-- Product Toolbar (Edit, Move, Delete Buttons) -->
        <div slot="extra">

            <div class="single-draggable-item-toolbox">

                <!-- Remove Product Button  -->
                <Icon type="ios-trash-outline" class="single-draggable-item-icon mr-2" size="20" @click="handleConfirmRemoveProduct()" />

                <!-- Edit Product Button  -->
                <Icon type="ios-create-outline" class="single-draggable-item-icon mr-2" size="20" @click="handleEditProduct()" />

                <!-- Copy Product Button  -->
                <Icon type="ios-copy-outline" class="single-draggable-item-icon mr-2" size="20" @click="handleCloneProduct()"/>

                <!-- Move Product Button  -->
                <Icon type="ios-move" class="single-draggable-item-icon dragger-handle mr-2" size="20" />
            
            </div> 

        </div>  

        <div v-show="isExpanded">

            <template v-if="product.allow_variants">

                <!-- If we are loading, Show Loader -->
                <Loader v-show="isLoading">Loading variations...</Loader>

                <template v-if="!isLoading">
                    
                    <span class="font-weight-bold d-block mt-2 mb-3">Variations:</span>

                    <!-- Variation List & Dragger  -->
                    <draggable
                        :list="productVariations"
                        @start="drag=true" 
                        @end="drag=false" 
                        :options="{
                            group:'products', 
                            handle:'.dragger-handle',
                            draggable:'.single-draggable-item',
                        }">

                        <!-- Single Product Variation  -->
                        <single-product v-for="(product, index) in productVariations" :key="index" name="single-product-variation"
                            :products="productVariations"
                            :location="location"
                            :product="product"
                            :store="store"
                            :index="index">
                        </single-product>
                        
                        <!-- No Product Variations message -->
                        <Alert v-if="!productVariationsExist" type="info" show-icon>
                            No Variations Found
                            <template slot="desc">
                            This product does not have any variations yet
                            </template>
                        </Alert>

                    </draggable>

                </template>

            </template>

            <template v-else>

                <!-- Product Details  -->
                <span class="d-flex">
                    <span class="font-weight-bold mr-2">Description: </span><br>
                    <span v-if="product.description">{{ product.description }}</span>
                    <span v-else class="text-info">No description</span>
                </span>

            </template>
                
        </div>

        <!-- 
            MODAL TO ADD / CLONE / EDIT STATIC OPTION
        -->
        <template v-if="isOpenManageProductDrawer">

            <manageProductDrawer
                :index="index"
                :store="store"
                :product="product"
                :location="location"
                :products="products"
                :isCloning="isCloning"
                :isEditing="isEditing"
                @visibility="isOpenManageProductDrawer = $event">
            </manageProductDrawer>

        </template>

    </Card>

</template>

<script>

    import draggable from 'vuedraggable';
    import manageProductDrawer from './manageProductDrawer.vue';
    import Loader from './../../../../../../../components/_common/loaders/default.vue';

    export default {
        components: { draggable, manageProductDrawer, Loader },
        props: {
            index: {
                type: Number,
                default:null
            },
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
            },
            products: {
                type: Array,
                default:() => []
            }
        },
        data(){

            return {
                isOpenManageProductDrawer: false,
                productVariations: [],
                isExpanded: false,
                isEditing: false,
                isCloning: false,
                isLoading: false,
            }
        },
        computed: {
            numberOfVariations(){
                return this.product['_links']['bos:variations'].total;
            },
            productVariationsExist(){
                return this.numberOfVariations ? true : false;
            },
            variationsUrl(){
                return this.product['_links']['bos:variations'].href;
            },
            cardClasses(){
                return [
                    'single-draggable-item', 
                    (this.isExpanded ? 'active' : ''), 'mb-2'
                ]
            },
            arrowDirection(){
                return this.isExpanded ? 'ios-arrow-down' : 'ios-arrow-forward';
            },
            isValidProduct(){

                //  No variations found
                if( this.product.allow_variants && !this.productVariationsExist ){
                    
                    return false;

                }else if( !this.product.allow_variants ){

                    //  No unit regular price set
                    if( !this.product.unit_regular_price ){
                        
                        return false;

                    //  No quantity
                    }else if( this.product.allow_stock_management && !this.product.stock_quantity ){
                        
                        return false;

                    }

                }

                return true;

            }
        },
        methods: {
            toggleExpansion(){
                this.isExpanded = !this.isExpanded;

                //  If the toggle is eexpanded and the product allows variants
                if( this.isExpanded && this.product.allow_variants ){

                    //  Get the product variations
                    this.fetchProductVariations();
                }
            },
            handleEditProduct(){
                this.isCloning = false;
                this.isEditing = true;
                this.handleOpenManageProductModal();
            },
            handleCloneProduct() {
                this.isCloning = true;
                this.isEditing = false;
                this.handleOpenManageProductModal();
            },
            handleConfirmRemoveProduct(){

                const self = this;

                //  Make a popup confirmation modal so that we confirm the product removal
                this.$Modal.confirm({
                    width: '450',
                    closable: true,
                    okText: 'Delete',
                    cancelText: 'Cancel',
                    title: 'Delete Product',
                    onOk: () => { this.handleRemoveProduct() },
                    render: function (h) {
                        return h(
                            'span', [
                                'Are you sure you want to delete "',
                                h('span', { class: ['font-weight-bold'] }, self.product.name),
                                '". After this product is deleted you cannot recover it again.'
                            ]
                        )
                    }
                });
            },
            handleRemoveProduct() {

                //  Remove product from list
                this.products.splice(this.index, 1);

                //  Product deleted success message
                this.$Message.success({
                    content: 'Product deleted!',
                    duration: 6
                });
            },
            fetchProductVariations() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                //  Use the api call() function, refer to api.js
                api.call('get', this.variationsUrl)
                    .then(({data}) => {
                        
                        //  Console log the data returned
                        console.log(data);

                        //  Get the product variations
                        self.productVariations = data['_embedded']['products'] || [];

                        //  Stop loader
                        self.isLoading = false;

                    })         
                    .catch(response => { 

                        //  Log the responce
                        console.error(response);

                        //  Stop loader
                        self.isLoading = false;

                    });
                
            },
            handleOpenManageProductModal(){
                this.isOpenManageProductDrawer = true;
            }
        }
    }

</script>
