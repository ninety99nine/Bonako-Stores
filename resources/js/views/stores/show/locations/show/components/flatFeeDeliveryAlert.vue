<template>

    <Alert v-if="!freeDelivery && hasDeliveryFlatFee" type="success" class="mt-2">

        <span>This location requires a <span :class="['font-weight-bold', 'text-success']">Flat Fee</span> of {{ deliveryFlatFee }} for delivery to all destinations</span>
    
    </Alert>

</template>

<script>

    import miscMixin from './../../../../../../components/_mixins/misc/main.vue';

    export default {
        mixins: [miscMixin],
        props: {
            location: {
                type: Object,
                default: null
            },
            currencySymbol: {
                type: String,
                default: null
            },
        },
        computed: {
            freeDelivery(){
                return this.location.allow_free_delivery;
            },
            hasDeliveryFlatFee(){
                return (this.location.delivery_flat_fee > 0);
            },
            deliveryFlatFee(){
                var delivery_flat_fee = this.location.delivery_flat_fee;

                return this.formatPrice(delivery_flat_fee, this.currencySymbol)
            }
        }
    };

</script>
