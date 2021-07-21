<template>

    <!-- Stock Poptip -->
    <Poptip trigger="hover" :title="stockName" :placement="placement" width="300">

        <List slot="content" size="small">

            <!-- Quantity -->
            <ListItem v-if="allowStockManagement">
                <span :class="[stockQuantity ? 'text-success' : 'text-danger', 'mr-2']" :style="{ fontSize: '20px' }">{{ stockQuantity }}</span> remaining
            </ListItem>

            <!-- Stock Description -->
            <ListItem>{{ stockDescription }}</ListItem>

        </List>

        <!-- Stock quantity -->
        <span v-if="allowStockManagement && stockQuantity">{{ stockQuantity }}</span>

        <!-- Stock name e.g Unlimited or No Stock -->
        <Badge v-else :text="stockName" :type="hasStock ? 'success' : 'warning'"></Badge>

    </Poptip>

</template>

<script>

    import miscMixin from './../../../../../../components/_mixins/misc/main.vue';

    export default {
        mixins: [miscMixin],
        props: {
            instantCart: {
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
            allowStockManagement(){
                return this.instantCart.allow_stock_management.status;
            },
            hasStock(){
                return this.instantCart._attributes.has_stock.status;
            },
            stockName(){
                return this.instantCart._attributes.has_stock.name;
            },
            stockDescription(){
                return this.instantCart._attributes.has_stock.description;
            },
            stockQuantity(){
                return this.instantCart.stock_quantity.value;
            }
        }
    };

</script>
