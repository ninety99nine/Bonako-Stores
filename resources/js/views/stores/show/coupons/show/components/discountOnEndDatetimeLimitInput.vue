<template>

    <FormItem prop="discount_on_end_datetime" :error="serverDiscountOnEtartDatetimeError" class="mb-3">

        <DatePicker :value="initialDate"
                    :disabled="!couponForm.allow_discount_on_end_datetime"
                    type="datetime" format="dd MMM yyyy HH:mm" class="w-100"
                    placeholder="Select start date and time"
                    @on-change="onChange"
                    :options="options">
        </DatePicker>

    </FormItem>

</template>

<script>

    import moment from 'moment';

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
                initialDate: null,
                options: {
                    disabledDate (date) {
                        return date && date.valueOf() < Date.now() - 86400000;
                    }
                }
            }
        },
        computed: {
            serverDiscountOnEtartDatetimeError(){
                return (this.serverErrors || [])['discount_on_end_datetime'];
            },
            moment: function () {
                return moment();
            }
        },
        methods: {
            onChange(datetime){

                //  Convert "31 Mar 2021 06:30" to "2021-03-31 06:30:00"
                this.couponForm.discount_on_end_datetime = moment(datetime, 'DD MMM YYYY HH:mm').format('YYYY-MM-DD HH:mm:ss');

            }
        },
        created(){

            //  If we have a date
            if( this.couponForm.discount_on_end_datetime ){

                //  Convert "2021-03-31 06:30:00" to "31 Mar 2021 06:30"
                this.initialDate = moment(this.couponForm.discount_on_end_datetime, 'YYYY-MM-DD HH:mm:ss').format('DD MMM YYYY HH:mm');

            }

        }
    };

</script>
