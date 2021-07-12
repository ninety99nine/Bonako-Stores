<template>

    <FormItem prop="start_at" :error="serverStartAtError" class="mb-3">

        <div class="d-flex">
            <span class="mr-2" :style="{ minWidth: 'max-content' }">Start Date</span>
            <DatePicker :value="initialDate"
                        :disabled="isLoading || (isEditing && !advertForm.reset_dates)"
                        type="datetime" format="dd MMM yyyy HH:mm" class="w-100"
                        placeholder="Select start date and time"
                        @on-change="onChange"
                        :options="options">
            </DatePicker>
        </div>

    </FormItem>

</template>

<script>

    import moment from 'moment';

    export default {
        props: {
            advertForm: {
                type: Object,
                default: null
            },
            isEditing: {
                type: Boolean,
                default: false
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
                initialDate: null,
                options: {
                    disabledDate (date) {
                        return date && date.valueOf() < Date.now() - 86400000;
                    }
                }
            }
        },
        computed: {
            serverStartAtError(){
                return (this.serverErrors || [])['start_at'];
            },
            moment: function () {
                return moment();
            }
        },
        methods: {
            onChange(datetime){

                //  Convert "31 Mar 2021 06:30" to "31/03/2021 06:30"
                this.advertForm.start_at = moment(datetime, 'DD MMM YYYY HH:mm').format('YYYY-MM-DD HH:mm:ss');

            }
        },
        created(){

            //  If we have a date
            if( this.advertForm.start_at ){

                //  Convert "31/03/2021 06:30" to "31 Mar 2021 06:30"
                this.initialDate = moment(this.advertForm.start_at, 'YYYY-MM-DD HH:mm:ss').format('DD MMM YYYY HH:mm');

            }

        }
    };

</script>
