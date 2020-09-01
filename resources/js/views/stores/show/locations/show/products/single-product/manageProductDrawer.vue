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
            @on-ok="saveProduct()"
            v-model="drawerVisible"
            @on-visible-change="detectClose">
               
            <productManager :store="store" :location="location" :product="product"
                            @createdProduct="handleCreatedProduct($event)">
            </productManager>

        </Drawer>
        
    </div>
</template>
<script>

    import productManager from './productManager.vue';
    import drawerMixin from './../../../../../../../components/_mixins/drawer/main.vue';

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
            product: {
                type: Object,
                default: null
            }
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
                //  If we have a product then use "Edit Product" otherwise "Create Product" as the title
                return this.product ? 'Edit Product' : 'Create Product';
            }
        },
        methods: {
            /** Note the closeModal() method is imported from the
             *  drawerMixin file. It handles the closing process 
             *  of the drawer
             */
            saveProduct(){
                this.$emit('clone');
                this.closeModal();
            },
            handleCreatedProduct(product){
                console.log('handleCreatedProduct 1');
                console.log(product);

                //  Notify parent of the product created
                this.$emit('createdProduct', product);
                            
                //  Close the drawer
                this.closeDrawer();

            }
        }
    }
</script>