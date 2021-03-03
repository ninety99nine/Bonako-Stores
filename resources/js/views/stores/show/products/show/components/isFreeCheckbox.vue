<template>

    <FormItem prop="is_free" :error="serverIsFreeError" class="mb-0">

        <Poptip trigger="hover" width="350" placement="top-start" word-wrap
                content="Is this a FREE prodcut?">
            <Checkbox v-model="productForm.is_free" :disabled="isLoading">This is a Free product</Checkbox>
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
            serverIsFreeError(){
                return (this.serverErrors || {}).is_free;
            }
        }
    };

</script>
