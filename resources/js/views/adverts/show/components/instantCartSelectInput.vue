<template>

    <FormItem prop="resource_id" :error="serverResourceIdError" :class="['mb-2']">

        <div class="d-flex">

            <span :class="['mr-2']" :style="{ minWidth: 'max-content' }">Instant cart</span>

            <!-- Select -->
            <Select v-model="selectedResourceId" :class="['w-100', 'mr-2']" :disabled="isLoadingInstantCarts"
                    :loading="isLoadingInstantCarts" placeholder="Select instant cart" searchable>
                <Option v-for="(instantCart, index) in instantCarts" :value="instantCart.id"
                        :key="index" :label="instantCart.name+' # '+instantCart.id">
                    <Badge :color="instantCart.active.status ? 'green' : 'red'"></Badge>
                    <span>{{ instantCart.name }}</span>
                    <span style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">
                        <span>{{ instantCart.id }}</span>
                    </span>
                </Option>
            </Select>

            <!-- Refresh Button -->
            <div :style="{ width: '40px' }">
                <Poptip trigger="hover" content="Refresh the instant carts" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchInstantCarts()">
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
            advertForm: {
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
            }
        },
        data(){
            return {
                instantCarts: [],
                isLoadingInstantCarts: false
            }
        },
        computed: {
            instantCartsUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:instant_carts'].href
            },
            selectedResourceId:{
                get(){
                    return (this.advertForm.resource_id);
                },
                set(selectedResourceId){

                    this.advertForm.resource_id  = selectedResourceId;

                }
            },
            serverResourceIdError(){
                return (this.serverErrors || {}).resource_id;
            },
        },
        methods: {
            fetchInstantCarts() {

                if( this.instantCartsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingInstantCarts = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.instantCartsUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoadingInstantCarts = false;

                            //  Set the instant carts
                            self.instantCarts = (((data || [])['_embedded'] || [])['instant_carts'] || []);

                        })
                        .catch(response => {

                            //  Stop loader
                            this.isLoadingInstantCarts = false;

                        });

                }

            }
        },
        created(){
            this.fetchInstantCarts();
        }
    };

</script>
