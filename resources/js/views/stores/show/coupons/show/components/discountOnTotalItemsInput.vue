<template>

    <FormItem prop="discount_on_total_items"
              :error="serverDiscountOnTotalItemsError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-start" word-wrap
                content="Enter minimum cart items?" class="poptip-w-100">
            <InputNumber v-model="couponForm.discount_on_total_items" size="small" class="w-100"
                         placeholder="100" :disabled="isLoading || !couponForm.allow_discount_on_total_items"
                         :min="0" @on-blur="onChange()" @on-change="onChange()">
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
            serverDiscountOnTotalItemsError(){
                return (this.serverErrors || {}).discount_on_total_items;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "discount_on_total_items"
                this.$nextTick(() => {

                    if(this.couponForm.discount_on_total_items === null || this.couponForm.discount_on_total_items === undefined){

                        this.couponForm.discount_on_total_items = 0;

                    }

                });

            }
        }
    };

</script>
