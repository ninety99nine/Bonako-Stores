<template>

    <!-- Stock Poptip -->
    <Poptip trigger="hover" :title="allowVariants ? '' : stockName" :placement="placement" width="300">

        <List slot="content" size="small">

            <!-- Stock on variations -->
            <template v-if="allowVariants">Stock on variations</template>

            <!-- Stock Information -->
            <template v-else>
                <!-- Quantity -->
                <ListItem v-if="allowStockManagement">
                    <span :class="[stockQuantity ? 'text-success' : 'text-danger', 'mr-2']" :style="{ fontSize: '20px' }">{{ stockQuantity }}</span> remaining
                </ListItem>
                <!-- Stock Description -->
                <ListItem>{{ stockDescription }}</ListItem>
                <!-- Auto Manage Stock -->
                <ListItem>
                    <span :class="autoManageStock ? 'text-success': 'text-info'">{{ autoManageStock ? 'Stock is managed automatically': 'Stock is managed manually' }} </span>
                </ListItem>
            </template>

        </List>
        
        <span v-if="allowVariants">N/A</span>

        <!-- Stock quantity -->
        <span v-else-if="allowStockManagement && stockQuantity">{{ stockQuantity }}</span>

        <!-- Stock name e.g Unlimited or No Stock -->
        <Badge v-else :text="stockName" :type="hasStock ? 'success' : 'warning'"></Badge>

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
            allowStockManagement(){
                return this.product.allow_stock_management.status;
            },
            autoManageStock(){
                return this.product.auto_manage_stock.status;
            },
            hasStock(){
                return this.product._attributes.has_stock.status;
            },
            stockName(){
                return this.product._attributes.has_stock.name;
            },
            stockDescription(){
                return this.product._attributes.has_stock.description;
            },
            stockQuantity(){
                return this.product.stock_quantity.value;
            }
        }
    };

</script>
