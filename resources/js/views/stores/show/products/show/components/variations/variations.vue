<template>

    <div>

        <!-- Loading Variations Loader -->
        <Loader v-if="isLoadingVariations" :loading="true" type="text" class="mt-2 mb-2">Loading variations...</Loader>

        <!-- Show Variations -->
        <div v-if="!this.isLoadingVariations && !this.isCreatingVariations && variations.length" class="mt-4">

            <!-- Single Product Variation  -->
            <singleVariation v-for="(variation, index) in variations" :key="index"
                :products="variations"
                :product="variation"
                :location="location"
                :index="index"
                :mask="false">
            </singleVariation>

        </div>

        <!-- Warning Message Alert -->
        <Alert v-if="!isLoading && !variations.length" type="warning" show-icon>
            <div class="d-flex">
                <span :style="{ minWidth: 'fit-content' }" :class="['font-weight-bold', 'mr-2']">No variations found</span>

                <!-- Show the description -->
                <Poptip trigger="hover" placement="top" word-wrap width="300"
                        content="This product does not have variations">

                    <!-- Show the info icon -->
                    <Icon type="ios-information-circle-outline" :size="16" />

                </Poptip>
            </div>
        </Alert>

    </div>

</template>

<script>

    import singleVariation from './singleVariation.vue';
    import Loader from './../../../../../../../components/_common/loaders/default.vue';

    export default {
        props: {
            product: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            isCreatingVariations: {
                type: Boolean,
                default: false
            },
        },
        components: { singleVariation, Loader },
        data(){
            return {
                variations: [],
                isLoadingVariations: false
            }
        },
        watch: {
            //  Watch changes on isLoadingVariations
            isLoadingVariations (newVal, oldVal) {

                //  Notify parent on changes
                this.$emit('isLoadingVariations', newVal);

            }
        },
        computed: {
            variationsUrl(){
                if( this.product ){
                    return this.product['_links']['bos:variations'].href;
                }
            }
        },
        methods: {

            fetchVariations() {

                //  If we have the product url
                if( this.variationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingVariations = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.variationsUrl)
                        .then(({data}) => {

                            //  Get the product
                            self.variations = data['_embedded']['products'] || [];

                            self.$emit('variations', self.variations);

                            //  Stop loader
                            self.isLoadingVariations = false;

                        })
                        .catch(response => {

                            //  Stop loader
                            self.isLoadingVariations = false;

                        });
                }
            }

        },
        created(){

            //  Fetch the variations
            this.fetchVariations();

        }
    };

</script>
