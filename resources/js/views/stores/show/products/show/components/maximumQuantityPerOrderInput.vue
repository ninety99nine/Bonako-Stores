<template>

    <FormItem prop="maximum_quantity_per_order" :error="serverMaximumQuantityPerOrderError" class="mb-3">

        <Poptip trigger="focus" width="350" placement="top-end" word-wrap content="What is the maximum quantity per order?">
            <div :class="['d-flex', 'align-items-center']">
                <span :class="['mr-2']">Maximum: </span>
                <InputNumber v-model="productForm.maximum_quantity_per_order" size="small" class="w-100" placeholder="10" :min="minimum_quantity" :step="1"
                            :disabled="isLoading || !productForm.allow_maximum_quantity_per_order || !productForm.allow_multiple_quantity_per_order"
                            @on-blur="onChange()">
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
                minimum_quantity: 2
            }
        },
        computed: {
            serverMaximumQuantityPerOrderError(){
                return (this.serverErrors || {}).maximum_quantity_per_order;
            }
        },
        methods: {
            onChange(){
                if(this.productForm.maximum_quantity_per_order === null || this.productForm.maximum_quantity_per_order === undefined){

                    this.productForm.maximum_quantity_per_order = this.minimum_quantity;

                }
            }
        }
    };

</script>
