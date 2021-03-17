<template>

    <FormItem v-if="couponForm.allow_discount_on_minimum_total" prop="discount_on_minimum_total"
              :error="serverDiscountOnMinimumTotalError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-start" word-wrap
                content="Enter minimum cart total?">
            <InputNumber v-model="couponForm.discount_on_minimum_total" size="small" class="w-100"
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
            serverDiscountOnMinimumTotalError(){
                return (this.serverErrors || {}).discount_on_minimum_total;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "discount_on_minimum_total"
                this.$nextTick(() => {

                    if(this.couponForm.discount_on_minimum_total === null || this.couponForm.discount_on_minimum_total === undefined){

                        this.couponForm.discount_on_minimum_total = 0;

                    }

                });

            }
        }
    };

</script>
