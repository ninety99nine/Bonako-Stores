<template>

    <FormItem prop="type" :error="serverTypeError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Title -->
            <span :class="['mr-3']">Type: </span>

            <!-- Select -->
            <Select v-model="productForm.product_type_id" :class="['w-100', 'mr-2']"
                    :disabled="isLoadingProductTypes || isLoading">
                <Option v-for="(productType, index) in productTypes" :value="productType.id" :key="index">
                    {{ productType.name }}
                </Option>
            </Select>

            <!-- Refresh Button -->
            <div :style="{ width: '40px' }">
                <Poptip trigger="hover" content="Refresh the payment methods" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchProductTypes()">
                        <Icon type="ios-refresh" :size="20" />
                    </Button>
                </Poptip>
            </div>

        </div>

    </FormItem>

</template>

<script>

    export default {
        props: {
            productForm: {
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
        data(){
            return {
                productTypes: [],
                isLoadingProductTypes: false
            }
        },
        computed: {
            productTypesUrl(){
                /**
                 *  Note "api_home" is defined within the auth.js file.
                 *  It holds reference to common links for ease of access.
                 */
                return api_home['_links']['bos:product_types'].href;
            },
            serverTypeError(){
                return (this.serverErrors || {}).type;
            }
        },
        methods: {
            fetchProductTypes() {

                if( this.productTypesUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingProductTypes = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.productTypesUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoadingProductTypes = false;

                            //  Get the product types
                            self.productTypes = ((data || [])['_embedded'] || [])['product_types'];

                        })
                        .catch(response => {

                            //  Stop loader
                            this.isLoadingProductTypes = false;

                        });
                }

            },
        },
        created(){
            this.fetchProductTypes();
        }
    };

</script>
