<template>

    <FormItem label="Regular Price" prop="unit_regular_price" :error="serverRegularPriceError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-start" word-wrap
                content="What is your product/service usual price?">
            <InputNumber v-model="productForm.unit_regular_price" size="small" class="w-100"
                         placeholder="100" :disabled="isLoading" :min="0" @on-blur="onChange()">
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
                if(this.productForm.unit_regular_price === null || this.productForm.unit_regular_price === undefined){

                    this.productForm.unit_regular_price = 0;

                }
            }
        }
    };

</script>
