<template>

    <!-- Pricing Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <!-- Pricing on variations -->
            <template v-if="allowVariants">Pricing on variations</template>

            <!-- No price description -->
            <template v-else-if="isFree">{{ isFreeDescription }}</template>

            <!-- No price description -->
            <template v-else-if="!hasPrice">{{ priceDescription }}</template>

            <!-- Price breakdown -->
            <template v-else>
                <ListItem>Regular Price: {{ unitRegularPrice }}</ListItem>
                <ListItem>Sale Price: {{ unitSalePrice }}</ListItem>
                <ListItem class="text-danger">Discount: {{ unitSaleDiscount }}</ListItem>
                <ListItem class="text-danger">Cost: {{ unitCostPrice }}</ListItem>
                <ListItem class="text-success">Profit: {{ unitProfit }}</ListItem>
                <ListItem :class="['font-weight-bold', 'mt-2', 'mb-2']" :style="{ outline: 'double' }">Price: {{ unitPrice }}</ListItem>
            </template>

        </List>
        
        <span v-if="allowVariants">N/A</span>

        <!-- Free desclaimer -->
        <Badge v-else-if="isFree" text="Free" type="success"></Badge>

        <!-- No price warning -->
        <Badge v-else-if="!hasPrice" text="No Price" type="warning"></Badge>

        <!-- Price -->
        <span v-else>
            <span>{{ unitPrice }}</span>
        </span>

    </Poptip>

</template>

<script>

    import miscMixin from './../../../../../../components/_mixins/misc/main.vue';

    export default {
        mixins: [miscMixin],
        props: {
            product: {
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
            allowVariants(){
                return this.product.allow_variants.status;
            },
            isFree(){
                return this.product.is_free.status;
            },
            isFreeDescription(){
                return this.product.is_free.description;
            },
            onSale(){
                return this.product._attributes.on_sale.status;
            },
            hasPrice(){
                return this.product._attributes.has_price.status;
            },
            priceDescription(){
                return this.product._attributes.has_price.description;
            },
            unitRegularPrice(){
                return this.product.unit_regular_price.currency_money;
            },
            unitSalePrice(){
                return this.product.unit_sale_price.currency_money;
            },
            unitCostPrice(){
                return this.product.unit_cost.currency_money;
            },
            unitSaleDiscount(){
                return this.product._attributes.unit_sale_discount.currency_money;
            },
            unitProfit(){
                return this.product._attributes.unit_profit.currency_money;
            },
            unitLoss(){
                return this.product._attributes.unit_loss.currency_money;
            },
            unitPrice(){
                return this.product._attributes.unit_price.currency_money;
            }
        }
    };

</script>
