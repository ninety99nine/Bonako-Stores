<template>

    <FormItem prop="show_description" :error="serverShowDescriptionError" class="mb-3">

        <Poptip trigger="hover" width="350" placement="top-start" word-wrap
                content="Do you want the customer see this description?">
            <Checkbox v-model="productForm.show_description" :disabled="isLoading">Show description to customer</Checkbox>
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
            serverShowDescriptionError(){
                return (this.serverErrors || {}).show_description;
            }
        }
    };

</script>
