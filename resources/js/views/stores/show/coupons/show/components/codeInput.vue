<template>

    <FormItem v-if="couponForm.activation_type == 'use code'"
              prop="code" :error="serverCodeError" class="mb-0">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                content="Give your coupon a code e.g save10">
            <div class="d-flex">
                <Input type="text" v-model="couponForm.code" placeholder="Enter coupon code"
                       :disabled="isLoading" maxlength="10"
                       show-word-limit class="w-100">
                    <span slot="prepend">Code</span>
                </Input>
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
            serverCodeError(){
                return (this.serverErrors || [])['code'];
            }
        }
    };

</script>
