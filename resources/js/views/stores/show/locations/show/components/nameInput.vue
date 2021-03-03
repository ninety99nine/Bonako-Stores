<template>

    <FormItem prop="name" :error="serverNameError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100" content="Give your location a name">
            <div class="d-flex">
                <span :class="['max-content-width', 'mr-2']">Name: </span>
                <Input type="text" v-model="locationForm.name" placeholder="Enter name"
                       :disabled="isLoading" maxlength="30" show-word-limit class="w-100">
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
            serverNameError(){
                return (this.serverErrors || []).name;
            }
        }
    };

</script>
