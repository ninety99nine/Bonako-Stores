<template>

    <FormItem prop="discount_rate_type" :error="serverDiscountRateTypeError" :class="['mb-2']">

        <!-- Select -->
        <Select v-model="couponForm.discount_rate_type" :class="['w-100']"
                :disabled="isLoading" placeholder="Select discount type">
            <Option v-for="(discountRateType, index) in discountRateTypes" :key="index"
                    :value="discountRateType.value" :label="discountRateType.name">
            </Option>
        </Select>

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
            }
        },
        data(){
            return {
                discountRateTypes: [
                    {
                        name: 'Percentage Rate',
                        value: 'Percentage'
                    },
                    {
                        name: 'Fixed Rate',
                        value: 'Fixed'
                    }
                ]
            }
        },
        computed: {
            serverDiscountRateTypeError(){
                return (this.serverErrors || {}).discount_rate_type;
            }
        }
    };

</script>
