<template>

    <FormItem prop="call_to_action" :error="serverCallToActionError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                content="Give your advert a all to action e.g Visit store">
            <div class="d-flex">
                <span :class="['mr-2']" :style="{ minWidth: 'max-content' }">Call to action</span>
                <Input type="text" v-model="advertForm.call_to_action" placeholder="Enter advert call to action"
                       :disabled="isLoading" maxlength="50" show-word-limit class="w-100">
                </Input>
            </div>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            advertForm: {
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
                return (this.serverErrors || [])['call_to_action'];
            }
        }
    };

</script>
