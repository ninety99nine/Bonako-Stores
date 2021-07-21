<template>

    <FormItem prop="coupon_ids" :error="serverCouponIdsError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Select -->
            <Select v-model="selectedCouponIds" multiple :class="['w-100', 'mr-2']"
                    :disabled="isLoadingCoupons" :loading="isLoadingCoupons" placeholder="Select coupons">
                <Option v-for="(coupon, index) in coupons" :value="coupon.id"
                        :key="index" :label="coupon.name">
                    <span>{{ coupon.name }}</span>
                </Option>
            </Select>

            <!-- Refresh Button -->
            <div :style="{ width: '40px' }">
                <Poptip trigger="hover" content="Refresh the coupons" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchLocationCoupons()">
                        <Icon type="ios-refresh" :size="20" />
                    </Button>
                </Poptip>
            </div>

        </div>

    </FormItem>

</template>

<script>

    export default {
        props: {
            instantCartForm: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            isLoadingCoupons: {
                type: Boolean,
                default: false
            },
            coupons: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            parentFetchLocationCoupons: {
                type: Function,
                default: null
            },
        },
        data(){
            return {

            }
        },
        computed: {
            serverCouponIdsError(){
                return (this.serverErrors || {}).coupon_ids;
            },
            selectedCouponIds:{
                get(){
                    return (this.instantCartForm || {}).coupons.map((coupon) => {
                        return coupon.id
                    });
                },
                set(selectedCouponIds){

                    this.instantCartForm.coupons = selectedCouponIds.map((selectedCouponId) => {

                        //  Search coupon on existing coupons (Only return id, name and quantity)
                        var matchedCoupons = this.coupons.filter((coupon) => {
                                return coupon.id === selectedCouponId
                            }).map((coupon) => {
                                return {
                                    id: coupon.id,
                                    code: coupon.code
                                }
                            });

                        //  Set the coupon to the first matched coupon
                        var matchedCoupon = matchedCoupons[0];

                        return matchedCoupon;

                    });

                }
            },
        },
        methods: {
            fetchLocationCoupons() {
                this.parentFetchLocationCoupons();
            }
        }
    };

</script>
