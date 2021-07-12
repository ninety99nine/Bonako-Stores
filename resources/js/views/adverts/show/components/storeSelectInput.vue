<template>

    <FormItem prop="resource_id" :error="serverResourceIdError" :class="['mb-2']">

        <div class="d-flex">

            <span class="mr-2">Store</span>

            <!-- Select -->
            <Select v-model="selectedResourceId" :class="['w-100', 'mr-2']" :disabled="isLoadingStores"
                    :loading="isLoadingStores" placeholder="Select store">
                <Option v-for="(store, index) in stores" :value="store.id"
                        :key="index" :label="store.name+' # '+store.id">
                    <Badge :color="store.online.status ? 'green' : 'red'"></Badge>
                    <span>{{ store.name }}</span>
                    <span style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">
                        <span>{{ store.id }}</span>
                    </span>
                </Option>
            </Select>

            <!-- Refresh Button -->
            <div :style="{ width: '40px' }">
                <Poptip trigger="hover" content="Refresh the stores" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchStores()">
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
                stores: [],
                isLoadingStores: false
            }
        },
        computed: {
            storesUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:stores'].href
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
            fetchStores() {

                if( this.storesUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingStores = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.storesUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoadingStores = false;

                            //  Set the stores
                            self.stores = (((data || [])['_embedded'] || [])['stores'] || []);

                        })
                        .catch(response => {

                            //  Stop loader
                            this.isLoadingStores = false;

                        });

                }

            }
        },
        created(){
            this.fetchStores();
        }
    };

</script>
