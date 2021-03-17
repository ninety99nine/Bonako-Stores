<template>

    <FormItem prop="allow_free_delivery" :error="serverAllowFreeDeliveryError" class="mb-0">

        <Poptip trigger="hover" width="350" placement="top-start" word-wrap
                content="Does this instart cart offer Free Delivery?">
            <Checkbox v-model="instantCartForm.allow_free_delivery" :disabled="isLoading">
                <span class="ml-1">Allow Free Delivery</span>
            </Checkbox>
        </Poptip>

    </FormItem>

</template>

<script>

    export default {
        props: {
            instantCartForm: {
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
            serverAllowFreeDeliveryError(){
                return (this.serverErrors || {}).allow_free_delivery;
            }
        }
    };

</script>
