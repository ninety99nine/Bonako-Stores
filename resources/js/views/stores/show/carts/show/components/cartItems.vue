<template>

    <!-- Cart Items Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <ListItem v-for="(item, index) in itemLines" :key="index" :class="['d-block']">

                <div>{{ getItemInfo(item) }}</div>
                <div v-if="itemHasSaleDiscount(item)" class="text-danger">{{ itemSaleDiscount(item) }}</div>

            </ListItem>

            <ListItem v-if="!totalItems">No items added</ListItem>

        </List>

        <span>{{ totalItems }}</span>

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
            itemLines(){
                return this.cart ? (this.cart._embedded.item_lines || []) : [];
            },
            totalItems(){
                return this.cart ? (this.cart.total_items || 0) : 0;
            }
        },
        methods: {
            getItemInfo(item){
                return item.quantity+'x('+item.name+')'+ ' for ' + item.sub_total.currency_money;
            },
            itemSaleDiscount(item){
                return item.sale_discount_total.currency_money+' sale discount';
            },
            itemHasSaleDiscount(item){
                return item.sale_discount_total.amount ? true : false;
            }
        }
    };

</script>
