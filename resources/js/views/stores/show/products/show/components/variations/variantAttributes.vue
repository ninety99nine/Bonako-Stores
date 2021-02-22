<template>

    <div>

        <!-- Variant Attributes -->
        <div :class="['bg-grey-light', 'border', 'mt-2', 'p-2']">

            <Col :span="6">
                <span class="font-weight-bold">Option name</span>
            </Col>

            <Col :span="16">
                <span class="font-weight-bold">Option values</span>
            </Col>

            <Row v-for="(attribute, index) in productForm.variant_attributes" :gutter="20" :key="index">

                <Col :span="6">

                    <!-- Attribute Name -->
                    <FormItem prop="option_name" class="mb-2">
                        <Poptip trigger="focus" width="300" placement="top-start" word-wrap
                                content="Provide an attribute name e.g Size or Material">
                            <Input v-model="productForm.variant_attributes[index].name" type="text" placeholder="e.g Sizes"
                                class="w-100" maxlength="50">
                            </Input>
                        </Poptip>
                    </FormItem>

                </Col>

                <Col :span="16">

                    <!-- Attribute Values -->
                    <FormItem prop="option_values" class="mb-2">
                        <Poptip trigger="focus" width="300" placement="top-end" word-wrap
                                content="Provide attribute values e.g Small, Medium, Large">
                            <vue-tags-input
                                v-model="variantAttributeValue[index]" :tags="variantAttributeTags(attribute.values)" class="w-100"
                                @tags-changed="updateVariantAttributeOptions($event, index)" placeholder="Add variation options"
                                @adding-duplicate="handleAddingDuplicate($event)"
                                avoid-adding-duplicates />
                        </Poptip>
                    </FormItem>

                </Col>

                <Col :span="2" v-if="(productForm.variant_attributes || {}).length > 1">

                    <!-- Remove Variant Button  -->
                    <Poptip confirm
                            title="Are you sure you want to remove this variant? After removing the variant we will delete all the current variations and create new ones."
                            ok-text="Yes" cancel-text="No" width="300" placement="left"
                            @on-ok="removeVariantAttribute(index)">
                        <Icon type="ios-trash-outline" size="20"/>
                    </Poptip>

                </Col>


            </Row>

            <Row :gutter="12">

                <Col v-if="!hasInvalidVariants" :span="24">

                    <!-- Alert -->
                    <Alert type="warning">Provide variation names and options</Alert>

                </Col>

                <Col :span="8">

                    <!-- Generate Variations Button  -->
                    <Poptip confirm
                            title="Create new variations?"
                            ok-text="Yes" cancel-text="No" width="300" placement="top-start"
                            @on-ok="generateVariations()">

                        <basicButton
                            :disabled="!hasInvalidVariants || isCreatingVariations"
                            type="success" size="small"
                            customClass="mt-3 mb-3"
                            :ripple="false">
                            Create Variations
                        </basicButton>
                    </Poptip>

                </Col>

                <Col :span="16" class="clearfix">
                    <basicButton
                        customClass="mt-3 mb-3" :style="{ width: 'fit-content', position:'relative' }"
                        @click.native="addVariantAttribute()"
                        :disabled="isCreatingVariations"
                        type="default" size="small"
                        class="float-right"
                        :ripple="false">
                        + Add Another Variant
                    </basicButton>
                </Col>
            </Row>

        </div>

        <!-- Creating Variations Loader -->
        <Loader v-if="isCreatingVariations" :loading="true" type="text" :class="['text-left', 'mt-2', 'mb-2']">Creating variations...</Loader>

    </div>

</template>

<script>

    import VueTagsInput from '@johmun/vue-tags-input';
    import Loader from './../../../../../../../components/_common/loaders/default.vue';
    import basicButton from './../../../../../../../components/_common/buttons/basicButton.vue';

    export default {
        props: {
            productForm: {
                type: Object,
                default: null
            },
            product: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        components: { VueTagsInput, Loader, basicButton },
        data(){
            return {
                variantAttributeValue: [],
                isCreatingVariations: false,
            }
        },
        watch: {
            //  Watch changes on isCreatingVariations
            isCreatingVariations (newVal, oldVal) {

                //  Notify parent on changes
                this.$emit('isCreatingVariations', newVal);

            }
        },
        computed: {
            variationsUrl(){
                if( this.product ){
                    return this.product['_links']['bos:variations'].href;
                }
            },
            hasInvalidVariants(){

                //  Check if we have variant attributes
                if((this.productForm.variant_attributes || {}).length){

                    for(var x=0; x < (this.productForm.variant_attributes || {}).length; x++){

                        //  Get the current variant key e.g size, color, material, e.t.c
                        let attribute_name = this.productForm.variant_attributes[x].name;

                        //  Get the current variant value e.g ["SM", "M", "L"], ["Blue", "Red"] or ["Cotton", "Nylon"]
                        let attribute_values = this.productForm.variant_attributes[x].values;

                        // If the name or options have not been set then this is not valid variant attribute
                        if( !attribute_name || !attribute_values.length ){

                            return false;

                        }
                    }
                }

                return true;
            }
        },
        methods: {
            variantAttributeTags(variant_attributes){
                return variant_attributes.map(attribute => {
                    return {
                        text: attribute
                    }
                });
            },
            updateVariantAttributeOptions(tags, index){
                 this.productForm.variant_attributes[index].values = tags.map(tag => {
                    return tag.text
                });
            },
            addVariantAttribute(){

                if( this.productForm.variant_attributes == null ){

                    this.productForm.variant_attributes = [];

                }

                this.productForm.variant_attributes.push({  name: 'Color', values: ['Blue', 'Red'] });
            },
            removeVariantAttribute(index) {

                //  If we have more that one variant attribute
                if( this.productForm.variant_attributes.length > 1 ){

                    //  Remove the variant attribute
                    this.productForm.variant_attributes.splice(index, 1);

                    /** Update the product details. This is so that we can actually save the current
                     *  variant attributes of the product.
                     */
                    self.handleSubmit();

                    /** Re-fetch the product variations so that they can pick up the changes of the
                     *  parent variant attributes.
                     */
                    self.fetchVariations();

                }else{

                    this.$Notice.warning({

                        title: 'You must have atleast one variant'

                    });

                }

            },
            generateVariations() {

                //  Hold constant reference to the vue instance
                const self = this;

                //  Product data to update
                let updateData = self.productForm.variant_attributes;

                if( (updateData || []).length ){

                    //  Start loader
                    self.isCreatingVariations = true;

                    //  Use the api call() function located in resources/js/api.js
                    api.call('post', this.variationsUrl, updateData)
                        .then(({data}) => {

                            //  Console log the data returned
                            console.log(data);

                            //  Stop loader
                            self.isCreatingVariations = false;

                            /**
                             *  Note that we need to use the $nextTick() method to get the latest data of the
                             *  "isCreatingVariations". This is because when we $emit to the parent, the
                             *  "isCreatingVariations" uses its old value, however we need to wait for
                             *  it to be updated before we notify the parent on the latest updates.
                             */
                            self.$nextTick(() => {

                                //  Notify parent
                                self.$emit('generatedVariations');

                            });

                        })
                        .catch(response => {

                            //  Log the responce
                            console.log(response);

                            //  Stop loader
                            self.isCreatingVariations = false;
                        });

                }
            }
        }
    };

</script>
