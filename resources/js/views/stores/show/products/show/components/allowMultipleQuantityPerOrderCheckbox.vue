<template>

    <FormItem prop="allow_multiple_quantity_per_order" :error="serverAllowMultipleQuantityPerOrderError" class="mb-0">

        <Poptip trigger="hover" width="350" placement="top-start" word-wrap
                content="Do you want the customer to be able to enter the quantity of this product (How many they want) when ordering?">
            <Checkbox v-model="productForm.allow_multiple_quantity_per_order"
                    :disabled="isLoading">Allow multiple products per order</Checkbox>
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
            serverAllowMultipleQuantityPerOrderError(){
                return (this.serverErrors || {}).allow_multiple_quantity_per_order;
            }
        }
    };

</script>
