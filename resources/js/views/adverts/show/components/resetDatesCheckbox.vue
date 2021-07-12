<template>

    <FormItem prop="reset_dates" :error="serverResetDatesError" class="mb-2">

        <Checkbox v-model="advertForm.reset_dates" :disabled="isLoading" class="mr-0">
            <span :class="['mr-0', 'ml-1']">Reset dates</span>
        </Checkbox>

        <Poptip trigger="hover" width="350" placement="top-start" word-wrap :content="description">
            <Icon type="ios-information-circle-outline" :size="16" />
        </Poptip>

    </FormItem>

</template>

<script>

    export default {
        props: {
            advertForm: {
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
            serverResetDatesError(){
                return (this.serverErrors || {}).reset_dates;
            },
            description(){
                return this.advertForm.reset_dates
                    ? 'Uncheck to avoid reseting dates'
                    : 'Check to reset dates'
            }
        }
    };

</script>
