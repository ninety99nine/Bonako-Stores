<template>

    <FormItem prop="uses_code" :error="serverUsesCodeError" class="mb-0">

        <Checkbox v-model="couponForm.uses_code" :disabled="isLoading || couponForm.always_apply" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Uses code</span>
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
            serverUsesCodeError(){
                return (this.serverErrors || {}).uses_code;
            },
            description(){
                return this.couponForm.uses_code
                    ? 'Uncheck to apply this coupon without a code'
                    : 'Check to apply this coupon using a code e.g save10'
            }
        }
    };

</script>
