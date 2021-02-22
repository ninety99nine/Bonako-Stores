<template>

    <FormItem label="SKU (Stock Keeping Unit)" prop="sku" :error="serverSkuError" class="mb-3">
        <Poptip trigger="focus" width="380" placement="top-start" word-wrap
                content="Assign a unique number to this product to identify it for inventory management">
            <Input type="text" v-model="productForm.sku" :disabled="isLoading" placeholder="Enter unique code" class="w-100"></Input>
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
            serverSkuError(){
                return (this.serverErrors || {}).sku;
            }
        }
    };

</script>
