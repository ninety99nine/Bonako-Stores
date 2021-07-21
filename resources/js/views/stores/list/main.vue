<template>

    <Row :gutter="12">

        <Col :span="20" :offset="2">

            <Row :gutter="12">

                <Col :span="24">

                    <!-- Heading -->
                    <h1 :class="['text-center', 'border-bottom-dashed', 'py-3', 'mb-3']">My Stores</h1>

                </Col>

                <Col :span="24">

                    <div :class="['clearfix', 'my-3']">

                        <!-- Refresh Button -->
                        <Button type="default" size="default" :class="['float-right']"
                                @click.native="fetchStores()" :loading="isLoading"
                                :disabled="isLoading">
                            <Icon v-show="!isLoading" type="ios-refresh" :size="20" />
                            <span>Refresh</span>
                        </Button>

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" :class="['float-right', 'mr-2']"
                                @click.native="fetchStores()">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

                <Col :span="24">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isLoading" class="mt-5">Loading stores...</Loader>

                </Col>

                <template v-if="!isLoading">

                    <Col :span="8">

                        <!-- Add Store -->
                        <Card class="add-bos-mini-card-button mb-3"
                            @click.native="navigateToCreateStore()">
                            <div class="action-title">
                                <Icon type="ios-add" />
                                <span>Add Store</span>
                            </div>
                        </Card>

                        <!-- Stores Column 1 -->
                        <singleStoreCard v-for="(store, index) in firstColumnStores"
                            :key="index" :store="store" :stores="stores" @deleted="fetchStores">
                        </singleStoreCard>

                    </Col>

                    <Col :span="8">

                        <!-- Stores Column 2 -->
                        <singleStoreCard v-for="(store, index) in secondColumnStores"
                            :key="index" :store="store" :stores="stores" @deleted="fetchStores">
                        </singleStoreCard>

                    </Col>

                    <Col :span="8">

                        <!-- Stores Column 3 -->
                        <singleStoreCard v-for="(store, index) in thirdColumnStores"
                            :key="index" :store="store" :stores="stores" @deleted="fetchStores">
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

                            //  Stop loader
                            self.isLoading = false;

                        });
                }
            }
        },
        created(){

            //  Fetch the store
            this.fetchStores();

            //  Change dashboard heading
            this.$emit('changeHeading', 'Bonako Online');

        }
    }
</script>
