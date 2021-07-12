<template>

    <FormItem prop="apply_discount" :error="serverApplyDiscountError" class="mb-2">

        <Checkbox v-model="couponForm.apply_discount" :disabled="isLoading" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Apply discount</span>
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
            serverApplyDiscountError(){
                return (this.serverErrors || {}).apply_discount;
            },
            description(){
                return this.couponForm.apply_discount
                    ? 'Turn off to stop applying a discount'
                    : 'Turn on to always apply a discount'
            }
        }
    };

</script>
