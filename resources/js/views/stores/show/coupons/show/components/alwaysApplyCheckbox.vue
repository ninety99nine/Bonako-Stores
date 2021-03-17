<template>

    <FormItem prop="always_apply" :error="serverAlwaysApplyError" class="mb-0">

        <Checkbox v-model="couponForm.always_apply" :disabled="isLoading" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Always Apply</span>
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
            serverAlwaysApplyError(){
                return (this.serverErrors || {}).always_apply;
            },
            description(){
                return this.couponForm.always_apply
                    ? 'Uncheck to stop applying this coupon for every cart (Applied manually)'
                    : 'Check to always apply this coupon for every cart (Applied automatically)'
            }
        }
    };

</script>
