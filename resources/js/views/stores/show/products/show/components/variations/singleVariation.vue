<template>

    <Card :class="['cursor-pointer', 'mb-1']" @click.native="toggleExpansion()">

        <div :class="['clearfix', 'mb-2']">

            <!-- Product Name  -->
            <span :class="['float-left', 'font-weight-bold']">{{ product.name }}</span>

            <!-- Edit Product Button  -->
            <Icon :class="['float-right', 'dragger-handle']" type="ios-create-outline" size="20"
                  @click.native.stop="handleOpenManageProductModal()" />

            <!-- Product Position  -->
            <span :class="['float-right', 'border-bottom', 'mr-5']"># {{ position }}</span>

        </div>

        <div>

            <Badge v-for="(variable, index) in variables" :key="index" :text="variable.value" type="info" :class="['mr-2']"></Badge>

            <small v-if="(this.product || {}).allow_variants" :class="['text-primary']">

                <!-- Has variations icon  -->
                <Icon type="ios-git-branch" size="16" />

                <!-- Has variations title  -->
                <span :class="['font-weight-bold']">Has Variations</span>

            </small>
        </div>

        <div v-if="isExpanded">

            <!-- If we allow variations -->
            <template v-if="product.allow_variants">

                <!-- Variations - Registered Globally -->
                <variations :product="product" @isLoadingVariations="isLoadingVariations = $event"></variations>

            </template>

        </div>

        <!--
            MODAL TO EDIT PRODUCT
        -->
        <template v-if="isOpenManageProductModal">

            <manageProductDrawer
                :product="product"
                layoutSize="small"
                @savedProduct="handleSavedProduct($event)"
                @fetchedProduct="handleFetchedProduct($event)"
                @visibility="isOpenManageProductModal = $event">
            </manageProductDrawer>

        </template>

    </Card>

</template>

<script>

    import manageProductDrawer from './../../../components/manageProductDrawer.vue';

    export default {
        props: {
            product: {
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
        components: { manageProductDrawer },
        data(){
            return {
                isExpanded: false,
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
            variables(){
                return this.product._embedded.variables;
            }
        },
        methods: {
            toggleExpansion(){
                this.isExpanded = !this.isExpanded;
            },
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
