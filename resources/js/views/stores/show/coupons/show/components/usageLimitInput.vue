<template>

    <FormItem prop="usage_limit" :error="serverUsageLimitError" class="mb-2">
        <Poptip trigger="click" width="350" placement="top-end" word-wrap class="poptip-w-100"
                content="Give your coupon a usage limit so that only a limited number of customerse.g 50">
            <div class="d-flex">
                <span class="mr-2">Limit</span>
                <InputNumber v-model="couponForm.usage_limit" class="w-100"
                             placeholder="100" :disabled="isLoading || !couponForm.allow_usage_limit"
                             :min="minimum" @on-blur="onChange()" @on-change="onChange()">
                </InputNumber>
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
                minimum: 1
            }
        },
        computed: {
            serverUsageLimitError(){
                return (this.serverErrors || {}).usage_limit;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "usage_limit"
                this.$nextTick(() => {

                    if(this.couponForm.usage_limit === null || this.couponForm.usage_limit === undefined){

                        this.couponForm.usage_limit = this.minimum;

                    }

                });

            }
        }
    };

</script>
