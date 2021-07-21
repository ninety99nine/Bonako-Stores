<template>

    <FormItem prop="allow_discount_on_months_of_the_year" :error="serverAllowDiscountOnMonthsOfTheYearError" class="mb-0">

        <Checkbox v-model="couponForm.allow_discount_on_months_of_the_year" :disabled="isLoading" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Allow discount on months of the year</span>
        </Checkbox>

        <Poptip trigger="hover" width="350" placement="top" word-wrap
                content="Is this coupon only active based on the month of the year?">
            <Icon type="ios-information-circle-outline" :size="16" />
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
            serverAllowDiscountOnMonthsOfTheYearError(){
                return (this.serverErrors || {}).allow_discount_on_months_of_the_year;
            }
        }
    };

</script>
