<template>

    <FormItem label="Description" prop="description" :error="serverDescriptionError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap
                content="Describe your product">
            <Input type="textarea" v-model="productForm.description" placeholder="Enter product description"
                    :disabled="isLoading" maxlength="140" show-word-limit class="w-100">
            </Input>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            productForm: {
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
