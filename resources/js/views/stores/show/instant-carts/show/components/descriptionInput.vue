<template>

    <FormItem label="Description" prop="description" :error="serverDescriptionError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                content="Describe your instant cart">
            <Input type="textarea" v-model="instantCartForm.description" placeholder="Enter instant cart description"
                    :disabled="isLoading" maxlength="140" show-word-limit class="w-100">
            </Input>
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
            serverDescriptionError(){
                return (this.serverErrors || {}).description;
            }
        }
    };

</script>
