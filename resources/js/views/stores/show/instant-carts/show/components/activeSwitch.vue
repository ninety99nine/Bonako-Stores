<template>

    <FormItem prop="active" :error="serverActiveError" :class="['clearfix', 'mb-0']">
        <div class="d-flex float-right">
            <span class="d-block font-weight-bold mr-2">Active: </span>
            <Poptip trigger="hover" title="Turn On/Off" placement="bottom-end" word-wrap width="300"
                    :content="description">
                <i-Switch v-model="instantCartForm.active" :disabled="isLoading" />
            </Poptip>
        </div>
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
            serverActiveError(){
                return (this.serverErrors || {}).active;
            },
            description(){
                return this.instantCartForm.active
                    ? 'Turn off to deactivate this instant cart'
                    : 'Turn on to activate this instant cart'
            }
        }
    };

</script>
