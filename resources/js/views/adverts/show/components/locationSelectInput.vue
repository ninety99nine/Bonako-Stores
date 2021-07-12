<template>

    <FormItem prop="resource_id" :error="serverResourceIdError" :class="['mb-2']">

        <div class="d-flex">

            <span class="mr-2">Location</span>

            <!-- Select -->
            <Select v-model="selectedResourceId" :class="['w-100', 'mr-2']" :disabled="isLoadingLocations"
                    :loading="isLoadingLocations" placeholder="Select location">
                <Option v-for="(location, index) in locations" :value="location.id"
                        :key="index" :label="location.name+' # '+location.id">
                    <Badge :color="location.online.status ? 'green' : 'red'"></Badge>
                    <span>{{ location.name }}</span>
                    <span style="color:#ccc" :class="['float-right', 'font-italic', 'mr-3']">
                        <span>{{ location.id }}</span>
                    </span>
                </Option>
            </Select>

            <!-- Refresh Button -->
            <div :style="{ width: '40px' }">
                <Poptip trigger="hover" content="Refresh the locations" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchLocations()">
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
                locations: [],
                isLoadingLocations: false
            }
        },
        computed: {
            locationsUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:locations'].href
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
            fetchLocations() {

                if( this.locationsUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoadingLocations = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.locationsUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoadingLocations = false;

                            //  Set the locations
                            self.locations = (((data || [])['_embedded'] || [])['locations'] || []);

                        })
                        .catch(response => {

                            //  Stop loader
                            this.isLoadingLocations = false;

                        });

                }

            }
        },
        created(){
            this.fetchLocations();
        }
    };

</script>
