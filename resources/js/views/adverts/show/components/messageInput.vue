<template>

    <FormItem prop="message" :error="serverMessageError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                content="Give your advert a message e.g Save 10% for every purchase">
            <div class="d-flex">
                <span :class="['mr-2']">Message</span>
                <Input type="textarea" v-model="advertForm.message" placeholder="Enter advert message"
                       :disabled="isLoading" maxlength="120" show-word-limit class="w-100">
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
            serverMessageError(){
                return (this.serverErrors || [])['message'];
            }
        }
    };

</script>
