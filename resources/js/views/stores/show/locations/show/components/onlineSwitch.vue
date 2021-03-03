<template>

    <FormItem prop="online" :error="serverOnlineError" :class="['clearfix', 'mb-3']">
        <div class="d-flex">
            <span class="d-block font-weight-bold mr-2">Online: </span>
            <Poptip trigger="hover" title="Turn On/Off" placement="top-start" word-wrap width="300"
                    content="Turn on to allow subscribers to access this store location">
                <i-Switch v-model="locationForm.online" :disabled="isLoading" />
            </Poptip>
        </div>
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
            serverOnlineError(){
                return (this.serverErrors || {}).online;
            }
        }
    };

</script>
