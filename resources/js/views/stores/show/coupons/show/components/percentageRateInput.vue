<template>

    <FormItem v-if="couponForm.discount_rate_type == 'Percentage'" prop="percentage_rate"
              :error="serverPercentageRateError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-end" word-wrap class="poptip-w-100"
                content="What is your coupon percentage rate amount? e.g 50">
            <div class="d-flex">
                <span class="mr-2">Rate</span>
                <InputNumber v-model="couponForm.percentage_rate" class="w-100"
                            placeholder="100" :disabled="isLoading" :min="0" :max="100"
                            @on-blur="onChange()" @on-change="onChange()">
                </InputNumber>
                <span class="ml-1">%</span>
            </div>
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
            serverPercentageRateError(){
                return (this.serverErrors || {}).percentage_rate;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "percentage_rate"
                this.$nextTick(() => {

                    if(this.couponForm.percentage_rate === null || this.couponForm.percentage_rate === undefined){

                        this.couponForm.percentage_rate = 0;

                    }

                });

            }
        }
    };

</script>
