<template>

    <FormItem v-if="couponForm.allow_discount_on_total_unique_items" prop="discount_on_total_unique_items"
              :error="serverDiscountOnTotalUniqueItemsError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-start" word-wrap
                content="Enter minimum unique cart items?">
            <InputNumber v-model="couponForm.discount_on_total_unique_items" size="small" class="w-100"
                         placeholder="100" :disabled="isLoading" :min="0"
                         @on-blur="onChange()" @on-change="onChange()">
                <span slot="prepend">Minimum Total</span>
            </InputNumber>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            couponForm: {
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
            serverDiscountOnTotalUniqueItemsError(){
                return (this.serverErrors || {}).discount_on_total_unique_items;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "discount_on_total_unique_items"
                this.$nextTick(() => {

                    if(this.couponForm.discount_on_total_unique_items === null || this.couponForm.discount_on_total_unique_items === undefined){

                        this.couponForm.discount_on_total_unique_items = 0;

                    }

                });

            }
        }
    };

</script>
