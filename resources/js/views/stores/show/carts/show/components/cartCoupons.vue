<template>

    <!-- Cart Coupons Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <ListItem v-for="(coupon, index) in couponLines" :key="index" :class="['d-block']">

                <div>

                    <!-- Coupon Name -->
                    <span>{{ coupon.name }}</span>

                    <!-- Percentage Rate -->
                    <template v-if="coupon.discount_rate_type.type == 'Percentage'">
                        <span> - {{ coupon.percentage_rate }}%</span>
                    </template>

                    <!-- Fixed Rate -->
                    <template v-if="coupon.discount_rate_type.type == 'Fixed'">
                        <span> - {{ coupon.fixed_rate.currency_money }}</span>
                        <span :class="['font-weight-bold', 'mr-2']">(Fixed Rate)</span>
                    </template>

                </div>

            </ListItem>

            <ListItem v-if="!totalCoupons">No coupons applied</ListItem>

        </List>

        <span>{{ totalCoupons }}</span>

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
            couponLines(){
                return this.cart ? (this.cart._embedded.coupon_lines || []) : [];
            },
            totalCoupons(){
                return this.cart ? this.couponLines.length : 0;
            }
        },
        methods: {
            itemSaleDiscount(coupon){
                return coupon.sale_discount_total.currency_money+' sale discount';
            },
            itemHasSaleDiscount(coupon){
                return coupon.sale_discount_total.amount ? true : false;
            }
        }
    };

</script>
