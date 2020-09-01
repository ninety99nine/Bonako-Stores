<template>

    <Card @mouseover.native="isHovering = true" 
          @mouseout.native="isHovering = false"
          @click.native="navigateToViewStore()"
          class="bos-mini-card cursor-pointer mb-3" >
        
        <Row>

            <Col :span="24">

                <div class="d-flex pb-2">

                    <!-- Store Logo -->
                    <Avatar shape="square" :style="avatarStyles">
                        <span class="font-weight-bold">{{ store.name | firstLetter }}</span>
                    </Avatar>

                    <!-- Store Name: Note "firstLetter" filter is registered as a custom mixin -->
                    <span class="cut-text font-weight-bold mt-2 ml-2">{{ store.name }}</span>

                </div>

                <div class="sce-mini-card-body mb-3 py-2 pl-2 pr-5">
                    
                    <span class="d-inline-block mr-2">
                        <Badge :text="statusText" :status="status"></Badge>
                        <!-- If we are offline and have a reason provided -->
                        <Poptip v-if="!store.online && store.offline_message" trigger="hover" :content="store.offline_message" word-wrap width="300">
                            <!-- Show the info icon with the information of why we are offline -->
                            <Icon type="ios-information-circle-outline" :size="16" />
                        </Poptip>
                    </span>

                    <span class="d-inline-block">
                        
                        <!-- Locations Button -->
                        <Button type="dashed" size="small" class="text-primary" @click.native.stop="navigateToViewStoreLocations()">
                            {{ numberOfLocations }} {{ numberOfLocations == 1 ? ' Location' : ' Locations' }}
                        </Button>

                    </span>

                </div>

                <transition name="slide-right-fade">

                    <div v-show="isHovering" class="store-footer clearfix">

                        <div class="float-right">
                            
                            <!-- Delete store -->
                            <Button type="dashed" size="small" class="text-danger" @click.native.stop="handleOpenDeleteStoreModal()">Delete</Button>

                            <!-- Clone store -->
                            <Button type="dashed" size="small" icon="ios-copy-outline" @click.native.stop="navigateToCloneStore()">Clone</Button>

                            <!-- View store -->
                            <Button type="dashed" size="small" class="text-primary" @click.native.stop="navigateToViewStore()">View</Button>

                        </div>

                    </div>

                </transition>

            </Col>

        </Row>

        <!-- 
            MODAL TO DELETE STORE
        -->
        <template v-if="isOpenDeleteStoreModal">

            <deleteStoreModal
                :index="index"
                :store="store"
                :stores="stores"
                @deleted="$emit('deleted')"
                @visibility="isOpenDeleteStoreModal = $event">
            </deleteStoreModal>

        </template>

    </Card>

</template>

<script>

    //  Get the custom mixin file
    var customMixin = require('./../../../../mixin.js').default;

    import deleteStoreModal from './deleteStoreModal.vue';

    export default {
        mixins: [customMixin],
        components: { deleteStoreModal },
        props: {
            index: {
                type: Number,
                default: null
            },
            store: {
                type: Object,
                default: null
            },
            stores: {
                type: Array,
                default:() => []
            }
        },
        data(){
            return {
                isHovering: false,
                isOpenDeleteStoreModal: false,
            }
        },
        computed: {
            storeUrl(){
                return this.store['_links']['self'].href;
            },
            encodedStoreUrl(){
                return encodeURIComponent(this.storeUrl);
            },
            numberOfLocations(){
                return this.store['_links']['bos:locations'].total;
            },
            hexColor(){
                /** Note that vue does not allow us to use filters inside the component props
                 *  e.g :style="{ background: (myProperty | Filter) }" or directly inside
                 *  computed properties e.g return (myProperty | Filter). We can only use 
                 *  filters inside interpolations e.g {{ myProperty | Filter }}, and you 
                 *  can't use interpolations as attributes e.g 
                 * 
                 *  :style="{ background: {{ (myProperty | Filter) }} }"
                 * 
                 *  To overcome this challenge we need to access the filter method directly
                 *  by accessing the current component Vue Instance then pass the data to
                 *  the filter method.
                 */
                return this.$options.filters.firstLetterColor(this.store.name);
            },
            avatarStyles(){
                return {
                    border: '1px solid ' + this.hexColor + ' !important',
                    background: this.hexColor + '20 !important',
                    color: this.hexColor + ' !important',
                }
            },
            statusText(){
                return this.store.online ? 'Online' : 'Offline'
            },
            status(){
                return this.store.online ? 'success' : 'error'
            }
        },
        methods: {
            navigateToViewStoreLocations(){
                
                if( this.storeUrl ){
                
                    //  Navigate to store locations
                    this.$router.push({ name: 'show-locations', params: { store_url: this.encodedStoreUrl } });

                }
                
            },
            navigateToCloneStore(){
                
                if( this.storeUrl ){
                
                    //  Navigate to clone store
                    this.$router.push({ name: 'create-store', query: { store_url: this.encodedStoreUrl } });

                }
                
            },
            navigateToViewStore(){
                
                if( this.storeUrl ){
                    
                    //  Navigate to show the store
                    this.$router.push({ name: 'show-store-overview', params: { store_url: this.encodedStoreUrl } });

                }

            },
            handleOpenDeleteStoreModal(){
                this.isOpenDeleteStoreModal = true;
            }
        },
    }
</script>
