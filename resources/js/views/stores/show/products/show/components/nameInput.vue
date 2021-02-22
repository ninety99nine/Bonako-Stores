<template>

    <FormItem prop="name" :error="serverNameError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap
                content="Give your product/service a name e.g Rice and chicken">
            <div class="d-flex">
                <span :class="['mr-2']">Name: </span>
                <Input type="text" v-model="productForm.name" placeholder="Enter product name"
                       :disabled="isLoading" maxlength="50" show-word-limit class="w-100">
                </Input>
            </div>
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
            serverNameError(){
                return (this.serverErrors || {}).name;
            }
        }
    };

</script>
