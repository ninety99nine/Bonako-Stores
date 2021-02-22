<template>

    <FormItem prop="allow_stock_management" :error="serverAllowStockManagementError" class="mb-0">

        <Poptip trigger="hover" width="350" placement="top-start" word-wrap
                content="Does your product/service have stock or limited items?">
            <Checkbox v-model="productForm.allow_stock_management" :disabled="isLoading">Allow Stock Management</Checkbox>
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
            serverAllowStockManagementError(){
                return (this.serverErrors || {}).allow_stock_management;
            }
        }
    };

</script>
