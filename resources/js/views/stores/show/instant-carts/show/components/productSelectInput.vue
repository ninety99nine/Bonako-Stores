<template>

    <FormItem prop="product_ids" :error="serverProductIdsError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Select -->
            <Select v-model="selectedItemIds" multiple :class="['w-100', 'mr-2']" :key="resetProductSelectorKey"
                    :disabled="isLoadingProducts" :loading="isLoadingProducts" placeholder="Select products">
                <Option v-for="(product, index) in products" :value="product.id"
                        :key="index" :label="getQuantity(product)+'x('+product.name+')'">
                    <span>{{ product.name }}</span>
                    <span style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">
                        <span v-if="getOnSaleStatus(product)" :class="['mr-2']">({{ getOnSaleName(product) }})</span>
                        <span>{{ getUnitPrice(product) }}</span>
                    </span>
                </Option>
            </Select>

            <!-- Refresh Button -->
            <div :style="{ width: '40px' }">
                <Poptip trigger="hover" content="Refresh the products" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchLocationProducts()">
                        <Icon type="ios-refresh" :size="20" />
                    </Button>
                </Poptip>
            </div>

        </div>

        <div class="clearfix">

            <!-- Manage Quantity Button -->
            <Button v-if="instantCartForm.items.length" type="primary" size="small" class="float-right mt-2"
                    @click.native="isOpenProductQuantityModal = true">
                <span>Manage Quantity</span>
            </Button>

        </div>

        <!--
            MODAL EDIT PRODUCT QUANTITY
        -->
        <template v-if="isOpenProductQuantityModal">

            <manageProductQuantityModal
                :instantCart="instantCartForm"
                @updated="handleUpdatedProductQuantity()"
                @visibility="isOpenProductQuantityModal = $event">
            </manageProductQuantityModal>

        </template>

    </FormItem>

</template>

<script>

    import manageProductQuantityModal from './manageProductQuantityModal.vue';

    export default {
        props: {
            instantCartForm: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            isLoadingProducts: {
                type: Boolean,
                default: false
            },
            products: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            parentFetchLocationProducts: {
                type: Function,
                default: null
            },
        },
        components: {
            manageProductQuantityModal
        },
        data(){
            return {
                resetProductSelectorKey: 1,
                isOpenProductQuantityModal: false
            }
        },
        computed: {
            serverProductIdsError(){
                return (this.serverErrors || {}).product_ids;
            },
            selectedItemIds:{
                get(){
                    return (this.instantCartForm || {}).items.map((item) => {
                        return item.id
                    });
                },
                set(selectedItemIds){

                    this.instantCartForm.items = selectedItemIds.map((selectedItemId) => {

                        //  Search item on existing instant cart items
                        var matchedItems = this.instantCartForm.items.filter((item) => {
                                return item.id === selectedItemId
                            });

                        //  If we have matching items
                        if( matchedItems.length ){

                            //  Set the item to the first matched instant cart item
                            var matchedItem = matchedItems[0];


                        //  If we don't have matching items
                        }else{

                            //  Search item on existing products (Only return id, name and quantity)
                            var matchedProducts = this.products.filter((product) => {
                                    return product.id === selectedItemId
                                }).map((product) => {
                                    return {
                                        id: product.id,
                                        name: product.name,
                                        quantity: product.quantity
                                    }
                                });

                            //  If we have matching products
                            if( matchedProducts.length ){

                                //  Set the item to the first matched product
                                var matchedItem = matchedProducts[0];

                            }

                        }

                        return matchedItem;

                    });

                }
            },
        },
        methods: {
            handleUpdatedProductQuantity(){
                //  Reset the product selection key to force a re-render of the component
                ++this.resetProductSelectorKey;
            },
            fetchLocationProducts() {
                this.parentFetchLocationProducts();
            },
            getUnitPrice(product){
                return product['_attributes']['unit_price']['currency_money'];
            },
            getOnSaleStatus(product){
                return product['_attributes']['on_sale']['status'];
            },
            getOnSaleName(product){
                return product['_attributes']['on_sale']['name'];
            },
            getQuantity(product){
                //  Search item on existing instant cart items and return its quantity
                var matchedItems = this.instantCartForm.items.filter((item) => {
                        return item.id === product.id
                    });

                if( matchedItems.length ){

                    //  Set the first matched item
                    var matchedItem = matchedItems[0];

                    //  Return matched item quantity
                    return matchedItem['quantity'];

                }else{

                    return 1;

                }
            }
        }
    };

</script>
