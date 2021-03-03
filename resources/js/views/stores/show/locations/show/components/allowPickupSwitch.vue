<template>

    <FormItem prop="allow_pickups" :error="serverAllowPickupError" :class="['clearfix', 'mb-3']">
        <div class="d-flex">
            <span :class="['font-weight-bold', 'mr-2']">Allow Pickups: </span>
            <Poptip trigger="hover" title="Turn On/Off" placement="top-start" word-wrap width="300"
                    content="Turn on to allow customers to pickup their own orders">
                <i-Switch v-model="locationForm.allow_pickups" :disabled="isLoading" />
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
            serverAllowPickupError(){
                return (this.serverErrors || {}).allow_pickups;
            }
        }
    };

</script>
