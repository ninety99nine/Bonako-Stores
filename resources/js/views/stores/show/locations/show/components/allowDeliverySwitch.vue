<template>

    <FormItem prop="allow_delivery" :error="serverAllowDeliveryError" :class="['clearfix', 'mb-3']">
        <div class="d-flex">
            <span :class="['font-weight-bold', 'mr-2']">Allow Delivery: </span>
            <Poptip trigger="hover" title="Turn On/Off" placement="top-start" word-wrap width="300"
                    content="Turn on to allow delivery for orders placed to this location">
                <i-Switch v-model="locationForm.allow_delivery" :disabled="isLoading" />
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
            serverAllowDeliveryError(){
                return (this.serverErrors || {}).allow_delivery;
            }
        }
    };

</script>
