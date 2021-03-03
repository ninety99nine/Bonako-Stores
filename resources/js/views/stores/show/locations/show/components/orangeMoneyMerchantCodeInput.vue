<template>

    <FormItem prop="orange_money_merchant_code" :error="serverOrangeMoneyMerchantCodeError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100" content="Provide Orange Money merchant code to tranfer payments to your account">
            <div class="d-flex">
                <span :class="['max-content-width', 'mr-2']">Orange Money merchant code: </span>
                <Input type="text" v-model="locationForm.orange_money_merchant_code" placeholder="Enter Orange Money merchant code"
                       :disabled="isLoading" maxlength="10" show-word-limit class="w-100">
                </Input>
            </div>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            locationForm: {
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
            serverOrangeMoneyMerchantCodeError(){
                return (this.serverErrors || []).orange_money_merchant_code;
            }
        }
    };

</script>
