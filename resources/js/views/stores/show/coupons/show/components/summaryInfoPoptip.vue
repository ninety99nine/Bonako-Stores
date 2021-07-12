<template>

    <Poptip word-wrap width="400" trigger="hover" placement="top"
        :style="{ wordBreak: 'break-word', textAlign: 'start' }"
        title="Coupon Summary">

        <div slot="content">
            <div v-for="(couponProperty, index) in couponProperties" :key="index">

                <div v-if="couponProperty.active" class="mb-2">
                    <Icon :type="couponProperty.isValid ? 'ios-checkmark-circle' : 'ios-close-circle'"
                        :class="[couponProperty.isValid ? 'text-success' : 'text-danger', 'mr-1']"
                        :size="16" />
                    <span>{{ couponProperty.desc }}</span>
                </div>

            </div>
        </div>

        <Icon type="ios-information-circle-outline" :size="20" />

    </Poptip>

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
            coupon: {
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
                var discount_on_start_datetime = this.coupon.discount_on_start_datetime ? moment(this.coupon.discount_on_start_datetime, 'DD/MMM/YYYY HH:mm').format('DD MMM YYYY @ HH:mm') : null;

                //  Convert "31/Mar/2021 06:30" to "31 Mar 2021 06:30"
                var discount_on_end_datetime = this.coupon.discount_on_end_datetime ? moment(this.coupon.discount_on_end_datetime, 'DD/MMM/YYYY HH:mm').format('DD MMM YYYY @ HH:mm') : null;

                var discount_rate_type = (this.coupon.discount_rate_type.type == 'Percentage') ? 'percentage' : 'fixed';

                //  The formatPrice() method is defined from the miscMixin
                var discount_rate = (discount_rate_type == 'percentage') ? this.coupon.percentage_rate+'%' : this.formatPrice(this.coupon.fixed_rate.amount, this.locationCurrencySymbol);

                var usage_limit = this.coupon.usage_limit;

                var usage_quantity = this.coupon.usage_quantity;

                var quantity_remaining = (usage_limit - usage_quantity) > 0 ? (usage_limit - usage_quantity) : 0;

                return [
                    {
                        active: this.coupon.apply_discount.status,
                        desc: 'Allows a '+discount_rate_type+' discount of '+discount_rate,
                        isValid: true
                    },
                    {
                        active: this.coupon.allow_free_delivery.status,
                        desc: 'Allows free delivery to any destination',
                        isValid: true
                    },
                    {
                        active: this.coupon.activation_type.type == 'always apply',
                        desc: 'Applied on every cart placed to the store',
                        isValid: true
                    },
                    {
                        active: this.coupon.activation_type.type == 'use code',
                        desc: this.coupon.code
                                ? 'Only activated using the code "'+this.coupon.code+'"'
                                : 'This coupon does not have an activation CODE',
                        isValid: (this.coupon.code) ? true : false
                    },
                    {
                        active: this.coupon.allow_discount_on_minimum_total.status,
                        desc: 'Allows dicount if the cart total is '+this.formatPrice(this.coupon.discount_on_minimum_total, this.locationCurrencySymbol)+' or more',
                        isValid: true
                    },
                    {
                        active: this.coupon.allow_discount_on_total_items.status,
                        desc: 'Allows dicount if the cart has atleast '+this.coupon.discount_on_total_items+
                              ((this.coupon.discount_on_total_items == 1) ? ' item': ' items'),
                        isValid: true
                    },
                    {
                        active: this.coupon.allow_discount_on_total_unique_items.status,
                        desc: 'Allows dicount if the cart has atleast '+this.coupon.discount_on_total_unique_items+
                              ((this.coupon.discount_on_total_unique_items == 1) ? ' unique item': ' unique items'),
                        isValid: true
                    },
                    {
                        active: this.coupon.allow_usage_limit,
                        desc: 'Limited for use only '+usage_limit+
                                ((usage_limit == 1) ? ' time': ' times')+
                                ' ('+quantity_remaining + ((quantity_remaining == 1) ? ' coupon': ' coupons')+' left)',
                        isValid: true
                    },
                    {
                        active: this.coupon.allow_discount_on_start_datetime.status,
                        desc: discount_on_start_datetime ? 'Valid from '+discount_on_start_datetime : 'Provide the coupon starting date',
                        isValid: discount_on_start_datetime ? true : false
                    },
                    {
                        active: this.coupon.allow_discount_on_end_datetime.status,
                        desc: discount_on_end_datetime ? 'Valid till '+ discount_on_end_datetime : 'Provide the coupon ending date',
                        isValid: discount_on_end_datetime ? true : false
                    }
                ]
            }
        }
    };

</script>
