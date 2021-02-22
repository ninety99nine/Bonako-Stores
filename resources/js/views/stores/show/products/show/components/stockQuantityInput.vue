<template>

    <FormItem prop="stock_quantity" :error="serverStockQuantityError" class="mb-3">

        <Poptip trigger="focus" width="350" placement="top-end" word-wrap content="How much stock (Quantity) of this product/service do you have?">
            <div :class="['d-flex', 'align-items-center']">
                <span :style="{ width: '160px' }" :class="['mr-2']">Stock Quantity: </span>
                <InputNumber v-model="productForm.stock_quantity" size="small" class="w-100" placeholder="10"
                            :min="minimum_quantity" :step="1" :disabled="isLoading" @on-blur="onChange()">
                </InputNumber>
            </div>
        </Poptip>

    </FormItem>

</template>

<script>

    export default {
        props: {
            productForm: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        data(){
            return {
                minimum_quantity: 1
            }
        },
        computed: {
            serverStockQuantityError(){
                return (this.serverErrors || {}).stock_quantity;
            }
        },
        methods: {
            onChange(){
                if(this.productForm.stock_quantity === null || this.productForm.stock_quantity === undefined){

                    this.productForm.stock_quantity = this.minimum_quantity;

                }
            }
        }
    };

</script>
