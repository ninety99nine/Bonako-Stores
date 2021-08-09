<script>

    export default {
        computed: {
            customerStats(){
                return [
                    {
                        name: 'Sub total',
                        value: this.localCustomer.checkout_sub_total.currency_money
                    },
                    {
                        name: 'Discount total',
                        value: this.localCustomer.checkout_coupons_and_sale_discount_total.currency_money
                    },
                    {
                        name: 'Delivery fee total',
                        value: this.localCustomer.checkout_delivery_fee.currency_money
                    },
                    {
                        name: 'Grand total',
                        value: this.localCustomer.checkout_grand_total.currency_money
                    },
                    {
                        name: 'Total free cart delivery',
                        value: this.localCustomer.total_free_delivery_on_checkout
                    },
                    {
                        name: 'Total cart items',
                        value: this.localCustomer.checkout_total_items
                    },
                    {
                        name: 'Adverts used',
                        value: this.localCustomer.total_adverts_used_on_checkout
                    },
                    {
                        name: 'Coupons used',
                        value: this.localCustomer.total_coupons_used_on_checkout
                    },
                    {
                        name: 'Instant carts used',
                        value: this.localCustomer.total_instant_carts_used_on_checkout
                    },
                    {
                        name: 'Orders placed by store',
                        value: this.localCustomer.total_orders_placed_by_store
                    },
                    {
                        name: 'Orders placed by localCustomer',
                        value: this.localCustomer.total_orders_placed_by_localCustomer
                    }
                ]
            }
        }
    }
</script>
