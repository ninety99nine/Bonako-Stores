<template>

    <FormItem prop="allow_usage_limit" :error="serverAllowUsageLimitError" class="mb-0">

        <Checkbox v-model="couponForm.allow_usage_limit" :disabled="isLoading" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Allow usage limit</span>
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
            serverAllowUsageLimitError(){
                return (this.serverErrors || {}).allow_usage_limit;
            },
            description(){
                return this.couponForm.allow_usage_limit
                    ? 'Uncheck to stop limiting the number of times this coupon can be used'
                    : 'Check to limit the number of times that this coupon can be used'
            }
        }
    };

</script>
