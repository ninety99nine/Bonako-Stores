<template>

    <FormItem label="Description" prop="description" :error="serverDescriptionError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                content="Describe your coupon e.g Allows the customer to save 20%">
            <Input type="textarea" v-model="couponForm.description" placeholder="Enter coupon description"
                    :disabled="isLoading" maxlength="140" show-word-limit class="w-100">
            </Input>
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
            serverDescriptionError(){
                return (this.serverErrors || {}).description;
            }
        }
    };

</script>
