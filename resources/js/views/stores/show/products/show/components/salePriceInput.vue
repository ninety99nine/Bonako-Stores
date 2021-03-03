<template>

    <FormItem label="Sale Price" prop="unit_sale_price" :error="serverSalePriceError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-start" word-wrap
                content="Is your product/service on sale? Add your sale price">
            <InputNumber v-model="productForm.unit_sale_price" size="small" class="w-100"
                        :disabled="isLoading || productForm.is_free || !productForm.unit_regular_price" placeholder="80"
                        :max="maximumSalePrice" :min="0" :step="1" @on-blur="onChange()" @on-change="onChange()">
            </InputNumber>
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

            }
        },
        computed: {
            maximumSalePrice(){
                return this.productForm.unit_regular_price || 0;
            },
            serverSalePriceError(){
                return (this.serverErrors || {}).unit_sale_price;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "unit_sale_price"
                this.$nextTick(() => {

                    if(this.productForm.unit_sale_price === null || this.productForm.unit_sale_price === undefined){

                        this.productForm.unit_sale_price = 0;

                    }

                });
            }
        }
    };

</script>
