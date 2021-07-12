<template>

    <FormItem prop="allow_discount_on_start_datetime" :error="serverAllowDiscountOnStartDatetimeError" class="mb-0">

        <Checkbox v-model="couponForm.allow_discount_on_start_datetime" :disabled="isLoading" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Allow limit by start date</span>
        </Checkbox>

        <Poptip trigger="hover" width="350" placement="top-start" word-wrap :content="description">
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
            serverAllowDiscountOnStartDatetimeError(){
                return (this.serverErrors || {}).allow_discount_on_start_datetime;
            },
            description(){
                return this.couponForm.allow_discount_on_start_datetime
                    ? 'Uncheck to stop activating this coupon after a specific date and time'
                    : 'Check to activate this coupon only after a specific date and time'
            }
        }
    };

</script>
