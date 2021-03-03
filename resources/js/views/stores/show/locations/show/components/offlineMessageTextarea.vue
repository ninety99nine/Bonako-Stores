<template>

    <FormItem prop="offline_message" :error="serverOfflineMessageError" class="mb-0">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100" content="Give your location a name">
            <Input type="textarea" v-model="locationForm.offline_message" placeholder="Enter offline message"
                    :disabled="isLoading" maxlength="140" show-word-limit class="w-100">
            </Input>
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
            serverOfflineMessageError(){
                return (this.serverErrors || []).offline_message;
            }
        }
    };

</script>
