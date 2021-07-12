<template>

    <FormItem v-if="couponForm.discount_rate_type == 'Fixed'" prop="fixed_rate"
              :error="serverFixedRateError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-end" word-wrap class="poptip-w-100"
                content="What is your coupon fixed rate amount e.g 20?">
            <div class="d-flex">
                <span class="mr-2">Rate ({{ locationCurrencySymbol }})</span>
                <InputNumber v-model="couponForm.fixed_rate" class="w-100"
                            placeholder="100" :disabled="isLoading" :min="0"
                            @on-blur="onChange()" @on-change="onChange()">
                </InputNumber>
            </div>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            location: {
                type: Object,
                default: null
            },
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
            locationCurrency(){
                return (this.location.currency || {});
            },
            locationCurrencySymbol(){
                return this.locationCurrency.symbol;
            },
            serverFixedRateError(){
                return (this.serverErrors || {}).fixed_rate;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "fixed_rate"
                this.$nextTick(() => {

                    if(this.couponForm.fixed_rate === null || this.couponForm.fixed_rate === undefined){

                        this.couponForm.fixed_rate = 0;

                    }

                });

            }
        }
    };

</script>
