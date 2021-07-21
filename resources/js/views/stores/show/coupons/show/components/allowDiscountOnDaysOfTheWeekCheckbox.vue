<template>

    <FormItem prop="allow_discount_on_days_of_the_week" :error="serverAllowDiscountOnDaysOfTheWeekError" class="mb-0">

        <Checkbox v-model="couponForm.allow_discount_on_days_of_the_week" :disabled="isLoading" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Allow discount on days of the week</span>
        </Checkbox>

        <Poptip trigger="hover" width="350" placement="top" word-wrap
                content="Is this coupon only active based on the day of the week?">
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
            serverAllowDiscountOnDaysOfTheWeekError(){
                return (this.serverErrors || {}).allow_discount_on_days_of_the_week;
            }
        }
    };

</script>
