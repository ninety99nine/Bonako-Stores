<template>

    <!-- Cart Coupons Poptip -->
    <Poptip trigger="hover" :placement="placement" width="450">

        <div slot="content">

            <div v-if="totalCoupons" :class="['overflow-hidden', 'py-2']" :style="{ textAlign: 'left' }">

                <Row :gutter="12" :class="['border-bottom-dashed', 'pb-2', 'mb-2']">

                    <Col :span="8">
                        <span class="font-weight-bold">Name</span>
                    </Col>

                    <Col :span="8" :class="['text-center']">
                        <span class="font-weight-bold">Discount Rate</span>
                    </Col>

                    <Col :span="8" :class="['text-center']">
                        <span class="font-weight-bold">Free Delivery</span>
                    </Col>

                </Row>

                <Row v-for="(coupon, index) in couponLines" :key="index" :gutter="12">

                    <Col :span="8">

                        <!-- Coupon Name -->
                        <span>{{ coupon.name }}</span>

                    </Col>

                    <Col :span="8" :class="['text-center']">

                        <!-- Percentage Rate -->
                        <template v-if="coupon.apply_discount.status && coupon.discount_rate_type.type == 'Percentage'">
                            <span :class="['text-success']">({{ coupon.percentage_rate }}%)</span>
                        </template>

                        <!-- Fixed Rate -->
                        <template v-else-if="coupon.apply_discount.status && coupon.discount_rate_type.type == 'Fixed'">
                            <span :class="['text-success']">({{ coupon.fixed_rate.currency_money }} Fixed Rate)</span>
                        </template>

                        <!-- No Rate -->
                        <span v-else="coupon.apply_discount.status && coupon.discount_rate_type.type == 'Fixed'">N/A</span>

                    </Col>

                    <Col :span="8" :class="['text-center']">

                        <!-- Coupon Allows Free Delivery -->
                        <span>{{ coupon.allow_free_delivery.status ? 'Yes' : 'No' }}</span>

                    </Col>

                </Row>

            </div>

            <div v-else>No coupons applied</div>

        </div>

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
