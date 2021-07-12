<template>

    <FormItem prop="title" :error="serverTitleError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                content="Give your advert a title e.g Super Save">
            <div class="d-flex">
                <span :class="['mr-2']">Title</span>
                <Input type="text" v-model="advertForm.title" placeholder="Enter advert title"
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
            serverTitleError(){
                return (this.serverErrors || [])['title'];
            }
        }
    };

</script>
