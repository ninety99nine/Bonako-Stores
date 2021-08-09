<template>

    <DatePicker v-model="localDateRange" type="daterange" split-panels placeholder="Select date range" format="dd MMM yyyy" @on-change="onChange" :disabled="isLoading"></DatePicker>

</template>

<script>

    import moment from 'moment';

    export default {
        props: ['dateRange', 'isLoading'],
        data(){
            return {
                localDateRange: []
            }
        },
        computed: {
            serverDiscountOnStartDatetimeError(){
                return (this.serverErrors || [])['discount_on_start_datetime'];
            },
            moment: function () {
                return moment();
            }
        },
        methods: {
            onChange(datetime){

                //  Convert "2021-03-31" to "2021-03-31 00:00:00"
                var start_date = datetime[0] ? moment(datetime[0], 'DD MMM YYYY').format('YYYY-MM-DD 00:00:00') : '';
                var end_date = datetime[1] ? moment(datetime[1], 'DD MMM YYYY').format('YYYY-MM-DD 00:00:00') : '';

                this.$emit('updated', [
                    start_date, end_date
                ])

            }
        },
        created(){

            //  If we have a date range
            if( this.dateRange.length ){

                //  Convert "2021-03-31 00:00:00 to "2021-03-31"
                var start_date = moment(this.dateRange[0], 'YYYY-MM-DD HH:mm:ss').format('dd MMM yyyy');
                var end_date = moment(this.dateRange[1], 'YYYY-MM-DD HH:mm:ss').format('dd MMM yyyy');

                this.localDateRange = [
                    start_date, end_date
                ];

            }

        }
    };

</script>
