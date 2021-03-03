<template>

    <FormItem prop="allow_payments" :error="serverAllowPaymentsError" :class="['clearfix', 'mb-3']">
        <div class="d-flex">
            <span :class="['max-content-width', 'mr-2']">Allow Payments: </span>
            <Poptip trigger="hover" title="Turn On/Off" placement="top-start" word-wrap width="300"
                    content="Turn on to allow payments for orders placed to this location">
                <i-Switch v-model="locationForm.allow_payments" :disabled="isLoading" />
            </Poptip>
        </div>
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
            serverAllowPaymentsError(){
                return (this.serverErrors || {}).allow_payments;
            }
        }
    };

</script>
