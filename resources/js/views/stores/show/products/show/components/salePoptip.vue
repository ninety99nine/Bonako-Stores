<template>

    <!-- Sale Poptip -->
    <Poptip trigger="hover" :placement="placement" word-wrap :width="onSale ? 100 : 200">

        <template v-if="allowVariants">

            <span slot="content">Sale on variations</span>

            <span>N/A</span>

        </template>

        <template v-else-if="onSale">

            <span slot="content" :class="['font-weight-bold', 'text-success']">{{ salePercentage }}% off</span>

            <Badge :text="onSaleName" type="success"></Badge>

        </template>

        <template v-else>

            <span slot="content">{{ onSaleDescription }}</span>

            <span>{{ onSaleName }}</span>

        </template>

    </Poptip>

</template>

<script>

    export default {
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
            onSale(){
                return this.product._attributes.on_sale.status;
            },
            onSaleName(){
                return this.product._attributes.on_sale.name;
            },
            onSaleDescription(){
                return this.product._attributes.on_sale.description;
            },
            salePercentage(){
                return this.product._attributes.unit_sale_percentage;
            },
        }
    };

</script>
