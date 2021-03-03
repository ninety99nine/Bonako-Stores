<template>

    <FormItem label="Regular Price" prop="unit_regular_price" :error="serverRegularPriceError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-start" word-wrap
                content="What is your product/service usual price?">
            <InputNumber v-model="productForm.unit_regular_price" size="small" class="w-100"
                         placeholder="100" :disabled="isLoading || productForm.is_free"
                         :min="0" @on-blur="onChange()" @on-change="onChange()">
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
            serverRegularPriceError(){
                return (this.serverErrors || {}).unit_regular_price;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "unit_regular_price"
                this.$nextTick(() => {

                    if(this.productForm.unit_regular_price === null || this.productForm.unit_regular_price === undefined){

                        this.productForm.unit_regular_price = 0;

                    }

                    //  Adjust unit sale price is greater than the unit regular price
                    if( this.productForm.unit_regular_price < this.productForm.unit_sale_price ){
                        this.productForm.unit_sale_price = this.productForm.unit_regular_price;
                    }

                    //  Adjust unit cost price is greater than the unit regular price
                    if( this.productForm.unit_regular_price < this.productForm.unit_cost ){
                        this.productForm.unit_cost = this.productForm.unit_regular_price;
                    }

                });

            }
        }
    };

</script>
