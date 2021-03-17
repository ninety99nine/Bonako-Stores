<template>

    <Card :class="['single-product-variation', visibleStatus ? 'active' : '', 'cursor-pointer', 'mb-2']"
          :style="{ background: visibleStatus ? 'ghostwhite' : '' }"
          @click.native="handleOpenManageProductModal()">

        <div :class="['clearfix', 'mb-2']">

            <!-- Product Name  -->
            <span :class="['float-left', 'font-weight-bold']">{{ product.name }}</span>

            <!-- Edit Product Button  -->
            <Icon :class="['float-right', 'dragger-handle']" type="ios-create-outline" size="20"
                  @click.native.stop="handleOpenManageProductModal()" />

            <!-- Product Position  -->
            <span :class="['float-right', 'border-bottom', 'mr-3']"># {{ position }}</span>

            <!-- Product Unit Price  -->
            <span v-if="!allowVariants" :class="['float-right', 'mr-3']">

                <!-- Price Poptip -->
                <pricingPoptip :product="product" placement="top-end"></pricingPoptip>

            </span>

            <span v-if="onSale" :class="['float-right', 'mr-3']">

                <!-- Sale Explanation Poptip -->
                <Poptip trigger="hover" placement="top" word-wrap width="100">

                    <span  slot="content" :class="['font-weight-bold', 'text-success']">{{ salePercentage }}% off</span>

                    <Badge :key="index" text="Sale" type="success"></Badge>

                </Poptip>

            </span>

            <span v-if="!visibleStatus" :class="['float-right', 'mr-3']">

                <!-- Visible Explanation Poptip -->
                <Poptip trigger="hover" placement="top" word-wrap width="300" :content="product.visible.description">
                    <span :style="{ color: 'orange' }">
                        <Icon type="ios-eye-off" size="20" />
                        <span>{{ product.visible.name }}</span>
                    </span>
                </Poptip>

            </span>

        </div>

        <div>

            <!-- Variables  -->
            <span v-for="(variable, index) in variables" :key="index" :class="['d-inline-block', 'mr-2']">
                <Poptip trigger="hover" placement="top-start" word-wrap width="300" :content="variable.name+': '+variable.value">
                    <Badge :text="variable.value" type="info"></Badge>
                </Poptip>
            </span>

            <small v-if="this.allowVariants" :class="['text-primary']">

                <!-- Has variations icon  -->
                <Icon type="ios-git-branch" size="16" />

                <!-- Has variations title  -->
                <span :class="['font-weight-bold']">Has Variations</span>

            </small>

        </div>

        <!--
            MODAL TO EDIT PRODUCT
        -->
        <template v-if="isOpenManageProductModal">

            <manageProductDrawer
                :product="product"
                layoutSize="small"
                :location="location"
                @savedProduct="handleSavedProduct($event)"
                @fetchedProduct="handleFetchedProduct($event)"
                @visibility="isOpenManageProductModal = $event">
            </manageProductDrawer>

        </template>

    </Card>

</template>

<script>

    import statusBadge from '../statusBadge.vue';
    import pricingPoptip from './../pricingPoptip.vue';
    import miscMixin from './../../../../../../../components/_mixins/misc/main.vue';
    import manageProductDrawer from './../../../components/manageProductDrawer.vue';

    export default {
        mixins: [miscMixin],
        props: {
            product: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            products: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            index: {
                type: Number,
                default: null
            }
        },
        components: { statusBadge, pricingPoptip, manageProductDrawer },
        data(){
            return {
                isLoadingVariations: false,
                isOpenManageProductModal: false
            }
        },
        watch: {

        },
        computed: {
            position(){
                return (this.index + 1);
            },
            allowVariants(){
                return this.product.allow_variants.status;
            },
            variables(){
                return this.product._embedded.variables;
            },
            isFree(){
                return this.product.is_free.status;
            },
            onSale(){
                return this.product._attributes.on_sale.status;
            },
            currencySymbol(){
                return (this.location._embedded.currency || {}).symbol;
            },
            unitRegularPrice(){
                return this.product.unit_regular_price.currency_money;
            },
            unitSalePrice(){
                return this.product.unit_sale_price.currency_money;
            },
            unitCostPrice(){
                return this.product.unit_cost.currency_money
            },
            unitSaleDiscount(){
                return this.product._attributes.unit_sale_discount.currency_money
            },
            unitProfit(){
                return this.product._attributes.unit_profit.currency_money
            },
            unitPrice(){
                return this.product._attributes.unit_price;
            },
            salePercentage(){
                return this.product._attributes.unit_sale_percentage;
            },
            visibleStatus(){
                return this.product.visible.status;
            }
        },
        methods: {
            handleOpenManageProductModal(){
                this.isOpenManageProductModal = true;
            },
            handleSavedProduct(product){
                //  Update the product
                this.$set(this.products, this.index, product);
            },
            handleFetchedProduct(product){
                //  Update the product
                this.$set(this.products, this.index, product);
            },
        },
        created(){
        }
    };

</script>
