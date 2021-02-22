<template>

    <FormItem prop="allow_maximum_quantity_per_order" :error="serverAllowMaximumQuantityPerOrderError" class="mb-0">

        <Poptip trigger="hover" width="350" placement="top-end" word-wrap
                content="Do you want to limit the total quantity of this product when the customer is placing an order?">
            <Checkbox v-model="productForm.allow_maximum_quantity_per_order"
                    :disabled="isLoading || !productForm.allow_multiple_quantity_per_order">Limit maximum products per order</Checkbox>
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
            serverAllowMaximumQuantityPerOrderError(){
                return (this.serverErrors || {}).allow_maximum_quantity_per_order;
            }
        }
    };

</script>
