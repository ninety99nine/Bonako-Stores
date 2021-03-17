<template>

    <FormItem prop="name" :error="serverNameError" class="mb-3">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100"
                content="Give your coupon a name e.g Save 20%">
            <div class="d-flex">
                <span :class="['mr-2']">Name: </span>
                <Input type="text" v-model="couponForm.name" placeholder="Enter coupon name"
                       :disabled="isLoading" maxlength="50" show-word-limit class="w-100">
                </Input>
            </div>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            couponForm: {
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
                return (this.serverErrors || [])['name'];
            }
        }
    };

</script>
