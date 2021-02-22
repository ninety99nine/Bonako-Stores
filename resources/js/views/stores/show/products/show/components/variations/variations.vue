<template>

    <div>

        <!-- Loading Variations Loader -->
        <Loader v-if="isLoadingVariations" :loading="true" type="text" class="text-left mt-2 mb-2">Loading variations...</Loader>

        <div v-if="!isLoadingVariations && !isCreatingVariations" class="mt-4">

            <!-- Single Product Variation  -->
            <singleVariation v-for="(variation, index) in variations" :key="index" name="single-product-variation"
                :products="variations"
                :product="variation"
                :index="index"
                :mask="false">
            </singleVariation>

        </div>


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

                            //  Console log the data returned
                            console.log(data);

                            //  Get the product
                            self.variations = data['_embedded']['products'] || [];

                            //  Stop loader
                            self.isLoadingVariations = false;

                        })
                        .catch(response => {

                            //  Log the responce
                            console.error(response);

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
