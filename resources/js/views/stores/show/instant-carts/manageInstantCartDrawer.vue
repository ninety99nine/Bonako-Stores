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
            @on-ok="saveProductInstantCart()"
            @on-visible-change="detectClose">

            <Form ref="productForm" class="product-form" :model="productForm" :rules="productFormRules">
                            
                <!-- Set Active Status -->
                <FormItem prop="active" class="clearfix mb-0">
                    <div class="d-flex float-right">
                        <span class="d-block font-weight-bold mr-2">{{ statusText }}: </span>
                        <Poptip trigger="hover" title="Turn On/Off" placement="bottom-end" word-wrap width="300" 
                                content="Turn on to allow subscribers to use this instant cart">
                            <i-Switch v-model="productForm.active" :disabled="isCreating || isSavingChanges"/>
                        </Poptip>
                    </div>
                </FormItem>

                <!-- Enter Name -->
                <FormItem label="Name" prop="name" :error="serverNameError" class="mb-3">
                    <Poptip trigger="focus" placement="top" word-wrap 
                            content="Give your instant cart a name e.g Express Fries & Wings">
                        <Input type="text" v-model="productForm.name" placeholder="Enter instant cart name" 
                                :disabled="isCreating || isSavingChanges" maxlength="50" show-word-limit class="w-100">
                        </Input>
                    </Poptip>
                </FormItem>
                
                <!-- Enter Description -->
                <FormItem label="Description" prop="description" :error="serverDescriptionError" class="mb-3">
                    <Poptip trigger="focus" placement="top" word-wrap 
                            content="Describe your instant cart">
                        <Input type="textarea" v-model="productForm.description" placeholder="Enter instant cart description" 
                                :disabled="isCreating || isSavingChanges" maxlength="500" show-word-limit class="w-100">
                        </Input>
                    </Poptip>
                </FormItem>

                <!-- Products -->
                <div class="clearfix">

                    <Divider orientation="left" class="font-weight-bold mt-4">Products</Divider>
                    <div class="d-flex mb-2">
                        <Poptip trigger="hover" content="Which products do you want to add to this instant cart?" word-wrap class="poptip-w-100">
                            <Select v-model="locationForm.products" filterable multiple class="w-100">
                                <Option v-for="(product, index) in products" :value="product.id" :key="index">
                                    {{ product.name }}
                                </Option>
                            </Select>
                        </Poptip>
                    </div>

                    <!-- Refresh Button -->
                    <Button type="default" size="small" class="float-right mr-2" @click.native="fetchProducts()">
                        <Icon type="ios-pin-outline" :size="20" class="mr-1"/>
                        <span>Refresh</span>
                    </Button>
                    <!-- Manage Quantity Button -->
                    <div v-if="locationForm.products.length" class="clearfix mb-2">
                        <Button type="primary" size="small" class="float-right"
                                @click.native="isOpenManageDestinationsModal = true">
                            <Icon type="ios-pin-outline" :size="20" class="mr-1"/>
                            <span>Product Quantity</span>
                        </Button>
                    </div>

                </div>

                <!-- Coupons -->
                <div class="clearfix">

                    <Divider orientation="left" class="font-weight-bold mt-4">Coupons</Divider>
                    <div class="d-flex mb-2">
                        <Poptip trigger="hover" content="Which coupons do you want to apply to this instant cart?" word-wrap class="poptip-w-100">
                            <Select v-model="locationForm.coupons" filterable multiple class="w-100">
                                <Option v-for="(coupon, index) in coupons" :value="coupon.id" :key="index">
                                    {{ coupon.name }}
                                </Option>
                            </Select>
                        </Poptip>
                    </div>

                    <!-- Refresh Button -->
                    <Button type="default" size="small" class="float-right mr-2" @click.native="fetchCoupons()">
                        <Icon type="ios-pin-outline" :size="20" class="mr-1"/>
                        <span>Refresh</span>
                    </Button>

                </div>

            </Form>

        </Drawer>
        
    </div>
</template>
<script>

    import drawerMixin from './../../../../components/_mixins/drawer/main.vue';

    export default {
        mixins: [ drawerMixin ],
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            instantCart: {
                type: Object,
                default: null
            }
        },
        data(){
            return{
                isOpenManageDestinationsModal: false,
                products: [],
                coupons: []
            }
        },
        computed: {
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
            }
        },
        methods: {
            //  START


            //  END



            /** Note the closeModal() method is imported from the
             *  drawerMixin file. It handles the closing process 
             *  of the drawer
             */
            saveProductInstantCart(){
                this.$emit('clone');
                this.closeModal();
            },
            handleCreatedProduct(product){

                //  Notify parent of the product created
                this.$emit('createdProduct', product);
                            
                //  Close the drawer
                this.closeDrawer();

            },
            handleSavedProduct(product){

                //  Notify parent of the product saved
                this.$emit('savedProduct', product);
                            
                //  Close the drawer
                this.closeDrawer();

            },
        }
    }
</script>