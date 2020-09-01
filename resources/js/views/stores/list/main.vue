<template>

    <Row :gutter="12">
        
        <Col :span="20" :offset="2">

            <Row :gutter="12">

                <Col :span="24">

                    <h1 class="text-center border-bottom-dashed py-3 mb-3">My Stores</h1>

                </Col>

                <Col v-if="isLoading" :span="24">

                    <!-- If we are loading, Show Loader -->
                    <Loader class="mt-5">Loading stores...</Loader>

                </Col>

                <template v-else>
                    
                    <Col :span="8">

                        <Card class="add-bos-mini-card-button mb-3"
                            @click.native="navigateToCreateStore()">
                            <div class="action-title">
                                <Icon type="ios-add" />
                                <span>Add Store</span>
                            </div>
                        </Card>

                        <singleStoreCard v-for="(store, index) in firstColumnStores" 
                            :key="index" :index="index" :store="store" :stores="stores" @deleted="fetchStores">
                        </singleStoreCard>

                    </Col>

                    <Col :span="8">

                        <singleStoreCard v-for="(store, index) in secondColumnStores" 
                            :key="index" :index="index" :store="store" :stores="stores" @deleted="fetchStores">
                        </singleStoreCard>
                        
                    </Col>

                    <Col :span="8">

                        <singleStoreCard v-for="(store, index) in thirdColumnStores" 
                            :key="index" :index="index" :store="store" :stores="stores" @deleted="fetchStores">
                        </singleStoreCard>
                        
                    </Col>

                </template>

            </Row>

        </Col>

    </Row>

</template>

<script>
    
    import Loader from './../../../components/_common/loaders/default.vue';
    import singleStoreCard from './components/singleStoreCard.vue'; 

    export default {
        components: { Loader, singleStoreCard },
        data(){
            return {
                user: auth.getUser(),
                isLoading: false,
                stores: []
            }
        },

        computed: {
            storesUrl(){
                return this.user['_links']['bos:stores'].href;
            },
            firstColumnStores(){
                return this.stores.filter((store, index) => {
                    var position = (index + 1);
                    if( (position) == 3  || (position % 3) == 0 ){
                        return true;
                    }
                })
            },
            secondColumnStores(){
                return this.stores.filter((store, index) => {
                    var position = (index + 1);
                    if( (position) == 1  || (position % 3) == 1 ){
                        return true;
                    }
                })
            },
            thirdColumnStores(){
                return this.stores.filter((store, index) => {
                    var position = (index + 1);
                    if( (position) == 2 || (position % 3) == 2 ){
                        return true;
                    }
                })
            }
        },
        methods: {
            navigateToCreateStore(){
                
                //  Navigate to create new store
                this.$router.push({ name: 'create-store' });
                
            },
            fetchStores() {

                //  If we have the store url
                if( this.storesUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    //  Use the api call() function, refer to api.js
                    api.call('get', this.storesUrl)
                        .then(({data}) => {

                            //  Get the stores
                            self.stores = ((data || [])['_embedded'] || [])['stores'];

                            //  Stop loader
                            self.isLoading = false;

                        })         
                        .catch(response => { 

                            //  Log the responce
                            console.error(response);

                            //  Stop loader
                            self.isLoading = false;

                        });
                }
            }
        },
        created(){

            //  Fetch the store
            this.fetchStores();
            
        }
    }
</script>
