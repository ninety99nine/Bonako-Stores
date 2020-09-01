<template>

    <Card @mouseover.native="isHovering = true" 
          @mouseout.native="isHovering = false"
          @click.native="navigateToViewLocation()"
          class="bos-mini-card cursor-pointer mb-3" >
        
        <Row>

            <Col :span="24">

                <div class="d-flex pb-2">

                    <!-- Location Name -->
                    <span :style="locationNameStyles" class="font-weight-bold mt-2 ml-2">{{ location.name }}</span>

                </div>

                <div class="bos-mini-card-body mb-3 py-2 pl-2 pr-5"
                    :style="{ textAlignLast: (isOnline ? 'auto' : 'justify') }">

                    <!-- If offline, then show the offline message if provided -->
                    <span v-if="!isOnline && location.offline_message" class="d-inline-block">
                        
                        <Poptip trigger="hover" :content="location.offline_message"
                                placement="top-start" word-wrap width="300">

                            <!-- Show the location info -->
                            <Icon type="ios-information-circle-outline" :size="16" />
                            <span>Info</span>

                        </Poptip>

                    </span>

                    <!-- Online / Offline status -->
                    <Tag v-if="isOnline" color="green" class="ml-2">Online</Tag>
                    <Tag v-else color="warning" class="ml-2">Offline</Tag>

                </div>

                <transition name="slide-right-fade">

                    <div v-show="isHovering" class="location-footer clearfix">

                        <div class="float-right">
                            
                            <!-- Clone location -->
                            <Button type="dashed" size="small" icon="ios-copy-outline" @click.native.stop="navigateToCloneStore()">Clone</Button>

                            <!-- View location -->
                            <Button type="dashed" size="small" class="text-primary" @click.native.stop="navigateToViewLocation()">View</Button>

                        </div>

                    </div>

                </transition>

            </Col>

        </Row>
        
    </Card>

</template>

<script>

    //  Get the custom mixin file
    var customMixin = require('./../../../../../../mixin.js').default;

    export default {
        mixins: [customMixin],
        props: {
            index: {
                type: Number,
                default: null
            },
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            }
        },
        data(){
            return {
                isHovering: false
            }
        },
        computed: {
            isOnline(){
                return (this.location.online);
            },
            storeUrl(){
                return this.store['_links']['self'].href;
            },
            locationUrl(){
                return this.location['_links']['self'].href;
            },
            locationNameStyles(){
                return {
                    fontSize: '20px',
                }
            }
        },
        methods: {
            navigateToCloneStore(){

                //  Navigate to clone location
                this.$router.push({ name: 'create-location', 
                    params: { store_url: encodeURIComponent(this.storeUrl) },
                    query: { location_url: encodeURIComponent(this.locationUrl) }
                });
                
                
            },
            navigateToViewLocation(){
                /** Note that using router.push() or router.replace() does not allow us to make a
                 *  page refresh when visiting routes. This is undesirable at this moment since our 
                 *  parent component contains the <router-view />. When the page does not refresh, 
                 *  the <router-view /> is not able to receice the nested components defined in the 
                 *  route.js file. This means that we are then not able to render the nested 
                 *  components and present them. To counter this issue we must construct the 
                 *  href and use "window.location.href" to make a hard page refresh.
                 */
                var route = { 
                        name: 'show-location', params: { 
                            store_url: encodeURIComponent(this.storeUrl),
                            location_url: encodeURIComponent(this.locationUrl)
                        } 
                    };

                //  Contruct the full path url
                var href = window.location.origin + "/" + VueInstance.$router.resolve(route).href

                //  Visit the url
                window.location.href = href;
            }
        },
    }
</script>
