<template>

    <div>

        <!-- Variant Attributes -->
        <div :class="['rounded', 'border', 'mt-2', 'p-3']" :style="{ background: 'ghostwhite' }">

            <Row v-for="(attribute, index) in variantAttributesForm" :gutter="20" :key="index">

                <Col :span="24">

                    <!-- Attribute Position  -->
                    <small :class="['font-weight-bold', 'd-inline-block', 'mb-2']">Variation # {{ (index + 1) }}</small>

                    <!-- Attribute Instruction -->
                    <FormItem prop="option_instruction" class="mb-2">
                        <Poptip trigger="focus" width="300" placement="top-start" word-wrap
                                content="Provide attribute instruction e.g Select color">
                            <Input v-model="variantAttributesForm[index].instruction" type="text" placeholder="Instruction e.g Select color"
                                class="w-100" maxlength="50" :disabled="isCreatingVariations || isLoading">
                            </Input>
                        </Poptip>
                    </FormItem>

                </Col>

                <Col :span="6">

                    <!-- Attribute Name -->
                    <FormItem prop="option_name" class="mb-2">
                        <Poptip trigger="focus" width="300" placement="top-start" word-wrap
                                content="Provide attribute name e.g Size or Material">
                            <Input v-model="variantAttributesForm[index].name" type="text" placeholder="e.g Sizes"
                                class="w-100" maxlength="50" :disabled="isCreatingVariations || isLoading">
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
                                @tags-changed="updateVariantAttributeOptions($event, index)" placeholder="Add variation options" size="small"
                                :disabled="isCreatingVariations" />
                        </Poptip>
                    </FormItem>

                </Col>

                <Col :span="2" v-if="(variantAttributesForm || {}).length > 1">

                    <!-- Remove Variant Button  -->
                    <Poptip confirm
                            title="Are you sure you want to remove this variant? After removing the variant we will delete all the current variations and create new ones."
                            ok-text="Yes" cancel-text="No" width="350" placement="left"
                            @on-ok="removeVariantAttribute(index)">
                        <Icon type="ios-trash-outline" size="20" />
                    </Poptip>

                </Col>

                <!-- Divider (Show if not the last variant attribute) -->
                <Divider v-if="(index + 1) != variantAttributesForm.length" class="mt-2 mb-2"></Divider>

            </Row>

            <Row :gutter="12">

                <Col v-if="hasInvalidVariants" :span="24" class="mt-2">

                    <!-- Alert -->
                    <Alert type="warning">
                        <template v-if="this.hasEmptyVariantNamesOrValues">Provide variation names and options (Avoid empty names or values)</template>
                        <template v-else-if="this.hasDuplicateVariantNames">Avoid using duplicate option names</template>
                        <template v-else-if="this.hasDuplicateVariantValues">Avoid using duplicate option values</template>
                    </Alert>

                </Col>

                <Col :span="24" :class="['clearfix', 'mt-4']">

                    <span :class="['float-left']">

                        <!-- Generate Variations Button  -->
                        <Poptip confirm
                                title="Create new variations?"
                                ok-text="Yes" cancel-text="No" width="300" placement="top-start"
                                @on-ok="generateVariations()">

                            <basicButton
                                :disabled="isCreatingVariations || isLoading || !variantAttributesHasChanged || hasInvalidVariants"
                                :ripple="!isCreatingVariations && !isLoading && !hasInvalidVariants && variantAttributesHasChanged"
                                :style="{ width: 'fit-content', position:'relative' }"
                                type="success" size="small">
                                Create Variations
                            </basicButton>

                        </Poptip>

                    </span>

                    <basicButton
                        v-if="variantAttributesHasChanged"
                        :disabled="isCreatingVariations"
                        :class="['float-left', 'ml-2']"
                        type="default" size="small"
                        @click.native="reset()">
                        Cancel
                    </basicButton>

                    <basicButton
                        :disabled="isCreatingVariations || isLoading"
                        @click.native="addVariantAttribute()"
                        type="default" size="small"
                        class="float-right">
                        + Add Another Variant
                    </basicButton>
                </Col>

            </Row>

        </div>

        <!-- Disclaimer: Free Product -->
        <Alert v-if="!isCreatingVariations && !isLoading && !hasInvalidVariants && variantAttributesHasChanged && variations.length"
               type="warning" :class="['mt-2', 'py-3', 'px-2']">
            Note that creating variations will replace your existing variations with new variations. Only matching variations will not be removed.
        </Alert>

        <!-- Creating Variations Loader -->
        <Loader v-if="isCreatingVariations" :loading="true" type="text" :class="['mt-2', 'mb-2']">Creating variations...</Loader>

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
            variations: {
                type: Array,
                default: function(){
                    return [];
                }
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
                variantAttributesForm: null,
                variantAttributesBeforeChanges: null
            }
        },
        watch: {
            //  Watch changes on isCreatingVariations
            isCreatingVariations (newVal, oldVal) {

                //  Notify parent on changes
                this.$emit('isCreatingVariations', newVal);

            },
            //  Watch changes on variantAttributesHasChanged
            variantAttributesHasChanged (newVal, oldVal) {

                //  Notify parent on changes
                this.$emit('variantAttributesHasChanged', newVal);

            }
        },
        computed: {
            variationsUrl(){
                if( this.product ){
                    return this.product['_links']['bos:variations'].href;
                }
            },
            hasInvalidVariants(){
                return this.hasEmptyVariantNamesOrValues || this.hasDuplicateVariantNames || this.hasDuplicateVariantValues;
            },
            hasEmptyVariantNamesOrValues(){

                //  Check if we have variant attributes
                if((this.variantAttributesForm || []).length){

                    for(var x=0; x < this.variantAttributesForm.length; x++){

                        //  Get the current variant key e.g size, color, material, e.t.c
                        let attribute_name = this.variantAttributesForm[x].name;

                        //  Get the current variant value e.g ["SM", "M", "L"], ["Blue", "Red"] or ["Cotton", "Nylon"]
                        let attribute_values = this.variantAttributesForm[x].values;

                        // If the name or options have not been set then this is not valid variant attribute
                        if( !attribute_name || !attribute_values.length ){

                            return true;

                        }
                    }
                }

                return false;
            },
            hasDuplicateVariantNames(){

                //  Check if we have variant attributes
                if((this.variantAttributesForm || []).length){

                    var names = [];

                    for(var x=0; x < this.variantAttributesForm.length; x++){

                        //  Get the current variant key e.g size, color, material, e.t.c
                        let attribute_name = this.variantAttributesForm[x].name.toLowerCase();

                        //  Check if the name is already used
                        if( names.includes(attribute_name) ){

                            return true;

                        }

                        names.push(attribute_name);

                    }
                }

                return false;
            },
            hasDuplicateVariantValues(){

                //  Check if we have variant attributes
                if((this.variantAttributesForm || []).length){

                    var values = [];

                    for(var x=0; x < this.variantAttributesForm.length; x++){

                        //  Get the current variant value e.g ["SM", "M", "L"], ["Blue", "Red"] or ["Cotton", "Nylon"]
                        let attribute_values = this.variantAttributesForm[x].values;

                        for(var y=0; y < attribute_values.length; y++){

                            //  Get the current variant value e.g "SM", "M", "L", "Blue", "Red", "Cotton", "Nylon"
                            let attribute_value = attribute_values[y].toLowerCase();

                            //  Check if the name is already used
                            if( values.includes(attribute_value) ){

                                return true;

                            }

                            values.push( attribute_value );

                        }
                    }
                }

                return false;
            },
            variantAttributesHasChanged(){

                //  Check if the variant attributes has been modified
                var status = !_.isEqual(this.variantAttributesForm, this.variantAttributesBeforeChanges);

                return status;

            },
        },
        methods: {
            reset(){
                this.variantAttributesForm = _.cloneDeep(this.variantAttributesBeforeChanges);
            },
            getVariantAttributesForm(){

                //  Clone the product variant attributes Object into a new Object
                return _.cloneDeep(this.productForm.variant_attributes);

            },
            variantAttributeTags(variant_attributes){
                return variant_attributes.map(attribute => {
                    return {
                        text: attribute
                    }
                });
            },
            updateVariantAttributeOptions(tags, index){

                var lower_case_tags = [];

                //  Filter non-duplicate tags
                var tags = tags.filter(tag => {

                    var tag = tag.text.toLowerCase();

                    if( lower_case_tags.includes(tag) ){

                        this.$Message.warning({
                            content: 'The tag "'+tag+'" is a duplicate',
                            duration: 6
                        });

                        return false;

                    }

                    lower_case_tags.push(tag);

                    return true;

                });

                 this.variantAttributesForm[index].values = tags.map(tag => {
                    return tag.text
                });
            },
            addVariantAttribute(){

                if( this.variantAttributesForm == null ){

                    this.variantAttributesForm = [];

                }

                this.variantAttributesForm.push({
                    name: 'Color',
                    values: ['Blue', 'Red'],
                    instruction: 'Select option'
                });
            },
            removeVariantAttribute(index) {

                //  If we have more that one variant attribute
                if( this.variantAttributesForm.length > 1 ){

                    //  Remove the variant attribute
                    this.variantAttributesForm.splice(index, 1);

                }else{

                    this.$Notice.warning({

                        title: 'You must have atleast one variant'

                    });

                }

            },
            copyVariantAttributesBeforeUpdate(){

                //  Clone the variant attributes before any changes occur
                this.variantAttributesBeforeChanges = _.cloneDeep( this.variantAttributesForm );

            },
            generateVariations() {

                //  Hold constant reference to the vue instance
                const self = this;

                if( (self.variantAttributesForm || []).length ){

                    //  Start loader
                    self.isCreatingVariations = true;

                    let data = {
                            postData: self.variantAttributesForm
                        };

                    //  Use the api call() function located in resources/js/api.js
                    api.call('post', this.variationsUrl, data)
                        .then(({data}) => {

                            //  Stop loader
                            self.isCreatingVariations = false;

                            //  Copy the variant attributes before any updates occur
                            this.copyVariantAttributesBeforeUpdate();

                            /**
                             *  Note that we need to use the $nextTick() method to get the latest data of the
                             *  "isCreatingVariations". This is because when we $emit to the parent, the
                             *  "isCreatingVariations" uses its old value, however we need to wait for
                             *  it to be updated before we notify the parent on the latest updates.
                             */
                            self.$nextTick(() => {

                                //  Notify parent
                                self.$emit('generatedVariations', self.variantAttributesForm);

                            });

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isCreatingVariations = false;

                        });

                }
            }
        },
        created(){

            //  Set the form details
            this.variantAttributesForm = this.getVariantAttributesForm();

            //  Copy the variant attributes before any updates occur
            this.copyVariantAttributesBeforeUpdate();

            //  If the product does not already have variant attributes
            if( !(this.variantAttributesForm || []).length ){

                //  Add the default variable attributes
                this.addVariantAttribute();

            }

        }
    };

</script>
