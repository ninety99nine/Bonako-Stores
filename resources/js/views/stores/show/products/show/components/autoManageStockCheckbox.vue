<template>

    <FormItem prop="auto_manage_stock" :error="serverAutoManageStockError" class="mb-0">

        <Poptip trigger="hover" width="350" placement="top-end" word-wrap
                content="Allow the system to automatically update the number of stock remaining each time customers order or purchase this product/service? - If this is turned off then you must manually update the product quantity yourself">
            <Checkbox v-model="productForm.auto_manage_stock" :disabled="isLoading">Manage Stock Automatically</Checkbox>
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
            }
        },
        data(){
            return {

            }
        },
        computed: {
            serverAutoManageStockError(){
                return (this.serverErrors || {}).auto_manage_stock;
            }
        }
    };

</script>
