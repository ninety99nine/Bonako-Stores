<template>

    <!-- Order Pricing Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <ListItem>Sub Total: {{ subTotal }}</ListItem>
            <ListItem :class="['border-0', 'text-danger']">Sale Discount: {{ discountTotal }}</ListItem>
            <ListItem :class="['text-danger']">Coupon Discount: {{ couponTotal }}</ListItem>
            <ListItem :class="['border-0', deliveryFee ? '' : 'd-none']">Delivery Fee: {{ deliveryFee }}</ListItem>
            <ListItem :class="['font-weight-bold', 'mt-2']" :style="{ outline: 'double' }">Grand Total: {{ grandTotal }}</ListItem>

        </List>

        <span>{{ grandTotal }}</span>

    </Poptip>

</template>

<script>

    import localMixin from '../../../orders/show/_mixins/main.vue';

    export default {
        mixins: [ localMixin ],
        props: {
            cart: {
                type: Object,
                default: null
            },
            placement: {
                type: String,
                default: 'top'
            }
        },
        data(){
            return {

            }
        },
        computed: {
            subTotal(){
                return this.cart ? (this.cart.sub_total.currency_money || 0) : 0;
            },
            couponTotal(){
                return this.cart ? (this.cart.coupon_total.currency_money || 0) : 0;
            },
            discountTotal(){
                return this.cart ? (this.cart.sale_discount_total.currency_money || 0) : 0;
            },
            deliveryFee(){
                return this.cart ? (this.cart.delivery_fee.currency_money || 0) : 0;
            },
            grandTotal(){
                return this.cart ? (this.cart.grand_total.currency_money || 0) : 0;
            },
        }
    };

</script>
