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
            :mask="mask"
            :okText="okText"
            cancelText="Cancel"
            :title="drawerTitle"
            :maskClosable="false"
            @on-ok="closeDrawer()"
            v-model="drawerVisible"
            @on-visible-change="detectClose">

            <productManager :store="store" :location="location" :assignedLocations="assignedLocations" :layoutSize="layoutSize"
                            :product="product" :products="products" @createdProduct="handleCreatedProduct($event)"
                            @savedProduct="handleSavedProduct($event)" @fetchedProduct="handleFetchedProduct($event)"
                            @closeDrawer="closeDrawer()">
            </productManager>

        </Drawer>

    </div>
</template>
<script>

    //import productManager from './productManager.vue';
    import productManager from './../show/main.vue';
    import drawerMixin from './../../../../../components/_mixins/drawer/main.vue';

    export default {
        mixins: [ drawerMixin ],
        components: { productManager },
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
                type: String
            },
            mask: {
                type: Boolean,
                default: true
            },
        },
        data(){
            return{

            }
        },
        computed: {
            okText(){
                //  If we have a product then use "Save Product" otherwise "Create Product" as the ok text
                return this.product ? 'Save Product' : 'Create Product';
            },
            drawerTitle(){

                if( this.product ){

                    if( this.product['parent_product_id'] == null ){

                        return 'Edit Product';

                    }else{

                        return 'Edit Product Variation';

                    }

                }else{

                    return 'Create Product';

                }
            }
        },
        methods: {
            handleCreatedProduct(product){

                //  Notify parent of the product created
                this.$emit('createdProduct', product);

            },
            handleSavedProduct(product){

                //  Notify parent of the product saved
                this.$emit('savedProduct', product);

            },
            handleFetchedProduct(product){

                //  Notify parent of the product fetched
                this.$emit('fetchedProduct', product);

            },
        }
    }
</script>
