<template>

    <Alert type="success" class="mt-2">

        <span :class="['font-weight-bold', 'd-block', 'mb-2']">Coupon Summary</span>

        <div v-for="(couponProperty, index) in couponProperties" :key="index">

            <div v-if="couponProperty.active" class="d-flex mb-2">
                <Icon :type="couponProperty.isValid ? 'ios-checkmark-circle' : 'ios-close-circle'"
                      :class="[couponProperty.isValid ? 'text-success' : 'text-danger', 'mr-1']"
                      :size="16" />
                <span>{{ couponProperty.desc }}</span>
            </div>

        </div>

    </Alert>

</template>

<script>

    import miscMixin from './../../../../../../components/_mixins/misc/main.vue';
    import moment from 'moment';

    export default {
        mixins: [miscMixin],
        props: {
            location: {
                type: Object,
                default: null
            },
            couponForm: {
                type: Object,
                default: null
            }
        },
        data(){
            return {
            }
        },
        computed: {
            locationCurrency(){
                return (this.location.currency || {});
            },
            locationCurrencySymbol(){
                return this.locationCurrency.symbol;
            },
            couponProperties(){

                //  Convert "31/Mar/2021 06:30" to "31 Mar 2021 06:30"
                var discount_on_start_datetime = this.couponForm.discount_on_start_datetime ? moment(this.couponForm.discount_on_start_datetime, 'DD/MMM/YYYY HH:mm').format('DD MMM YYYY @ HH:mm') : null;

                //  Convert "31/Mar/2021 06:30" to "31 Mar 2021 06:30"
                var discount_on_end_datetime = this.couponForm.discount_on_end_datetime ? moment(this.couponForm.discount_on_end_datetime, 'DD/MMM/YYYY HH:mm').format('DD MMM YYYY @ HH:mm') : null;

                var discount_rate_type = (this.couponForm.discount_rate_type == 'Percentage') ? 'percentage' : 'fixed';

                //  The formatPrice() method is defined from the miscMixin
                var discount_rate = (discount_rate_type == 'percentage') ? this.couponForm.percentage_rate+'%' : this.formatPrice(this.couponForm.fixed_rate, this.locationCurrencySymbol);

                var usage_limit = this.couponForm.usage_limit;

                var usage_quantity = this.couponForm.usage_quantity;

                var quantity_remaining = (usage_limit - usage_quantity) > 0 ? (usage_limit - usage_quantity) : 0;

                var discount_on_times = this.couponForm.discount_on_times.map((time) => {
                    return (time+':00');
                }).join(', ');

                var discount_on_days_of_the_week = this.couponForm.discount_on_days_of_the_week.join(', ');

                var discount_on_days_of_the_month = this.couponForm.discount_on_days_of_the_month.map((day) => {
                    return (day < 10) ? '0'+day : day;
                }).join(', ');

                var discount_on_months_of_the_year = this.couponForm.discount_on_months_of_the_year.join(', ');

                return [
                    {
                        active: this.couponForm.apply_discount,
                        desc: 'Allows a '+discount_rate_type+' discount of '+discount_rate,
                        isValid: true
                    },
                    {
                        active: this.couponForm.allow_free_delivery,
                        desc: 'Allows free delivery to any destination',
                        isValid: true
                    },
                    {
                        active: this.couponForm.activation_type == 'always apply',
                        desc: 'Applied on every cart placed to the store',
                        isValid: true
                    },
                    {
                        active: this.couponForm.activation_type == 'use code',
                        desc: this.couponForm.code
                                ? 'Only activated using the code "'+this.couponForm.code+'"'
                                : 'This coupon does not have an activation CODE',
                        isValid: (this.couponForm.code) ? true : false
                    },
                    {
                        active: this.couponForm.allow_discount_on_minimum_total,
                        desc: 'Allows discount if the cart total is '+this.formatPrice(this.couponForm.discount_on_minimum_total, this.locationCurrencySymbol)+' or more',
                        isValid: true
                    },
                    {
                        active: this.couponForm.allow_discount_on_total_items,
                        desc: 'Allows discount if the cart has atleast '+this.couponForm.discount_on_total_items+
                              ((this.couponForm.discount_on_total_items == 1) ? ' item': ' items'),
                        isValid: true
                    },
                    {
                        active: this.couponForm.allow_discount_on_total_unique_items,
                        desc: 'Allows discount if the cart has atleast '+this.couponForm.discount_on_total_unique_items+
                              ((this.couponForm.discount_on_total_unique_items == 1) ? ' unique item': ' unique items'),
                        isValid: true
                    },
                    {
                        active: this.couponForm.allow_usage_limit,
                        desc: 'Limited for use only '+usage_limit+
                                ((usage_limit == 1) ? ' time': ' times')+
                                ' ('+quantity_remaining + ((quantity_remaining == 1) ? ' coupon': ' coupons')+' left)',
                        isValid: true
                    },
                    {
                        active: this.couponForm.allow_discount_on_start_datetime,
                        desc: discount_on_start_datetime ? 'Valid from '+discount_on_start_datetime : 'Provide the coupon starting date',
                        isValid: discount_on_start_datetime ? true : false
                    },
                    {
                        active: this.couponForm.allow_discount_on_end_datetime,
                        desc: discount_on_end_datetime ? 'Valid till '+ discount_on_end_datetime : 'Provide the coupon ending date',
                        isValid: discount_on_end_datetime ? true : false
                    },



                    {
                        active: this.couponForm.allow_discount_on_times,
                        desc: discount_on_times ? 'Allows discount only on the following times of the day ('+discount_on_times+')' : 'Select times of the day to discount',
                        isValid: discount_on_times ? true : false
                    },
                    {
                        active: this.couponForm.allow_discount_on_days_of_the_week,
                        desc: discount_on_days_of_the_week ? 'Allows discount only on the following days of the week ('+discount_on_days_of_the_week+')' : 'Select days of the week to discount',
                        isValid: discount_on_days_of_the_week ? true : false
                    },
                    {
                        active: this.couponForm.allow_discount_on_days_of_the_month,
                        desc: discount_on_days_of_the_month ? 'Allows discount only on the following days of the month ('+discount_on_days_of_the_month+')' : 'Select days of the month to discount',
                        isValid: discount_on_days_of_the_month ? true : false
                    },
                    {
                        active: this.couponForm.allow_discount_on_months_of_the_year,
                        desc: discount_on_months_of_the_year ? 'Allows discount only on the following months of the year ('+discount_on_months_of_the_year+')' : 'Select months of the year to discount',
                        isValid: discount_on_months_of_the_year ? true : false
                    },
                    {
                        active: this.couponForm.allow_discount_on_new_customer,
                        desc: 'Allows discount only for new customers',
                        isValid: true
                    },
                    {
                        active: this.couponForm.allow_discount_on_existing_customer,
                        desc: 'Allows discount only for existing customers',
                        isValid: true
                    }
                ]
            }
        }
    };

</script>
