<template>

    <FormItem prop="call_to_action" :error="serverCallToActionError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100" content="Enter location call to action e.g Buy Grocery, Order meat, e.t.c">
            <div class="d-flex">
                <span :class="['max-content-width', 'mr-2']">Call to action: </span>
                <Input type="text" v-model="locationForm.call_to_action" placeholder="Enter call to action e.g Buy Grocery"
                       :disabled="isLoading" maxlength="20" show-word-limit class="w-100">
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
            serverCallToActionError(){
                return (this.serverErrors || []).call_to_action;
            }
        }
    };

</script>
