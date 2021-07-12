<template>

    <FormItem prop="store_id" :error="serverStoreIdError" :class="['mb-2']">

        <div class="d-flex">

            <span class="mr-2">Store</span>

            <!-- Select -->
            <Select v-model="selectedStoreId" :class="['w-100', 'mr-2']" :disabled="isLoadingStores"
                    :loading="isLoadingStores" placeholder="Select stores">
                <Option v-for="(store, index) in stores" :value="store.id"
                        :disabled="selectedStoreIds.includes(store.id)"
                        :key="index" :label="store.name">
                    <Badge :color="store.online.status ? 'green' : 'red'"></Badge>
                    <span>{{ store.name }}</span>
                    <span v-if="getVisitShortCodeDialingCode(store)" style="color:#ccc"
                          :class="['float-right', 'font-italic', 'mr-3']">
                        <span>({{ getVisitShortCodeDialingCode(store) }})</span>
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
            popularStoreForm: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            isLoadingStores: {
                type: Boolean,
                default: false
            },
            stores: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            popularStores: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            parentFetchStores: {
                type: Function,
                default: null
            },
        },
        data(){
            return {

            }
        },
        computed: {
            serverStoreIdError(){
                return (this.serverErrors || {}).store_id;
            },
            selectedStoreId:{
                get(){
                    return (this.popularStoreForm.store_id);
                },
                set(selectedStoreId){

                    this.popularStoreForm.store_id  = selectedStoreId;

                }
            },
            selectedStoreIds(){
                return this.popularStores.map(function(popularStore){
                    return popularStore.store_id
                });
            },
        },
        methods: {
            getVisitShortCodeDialingCode(store){
                return (store['_attributes']['visit_short_code'] || {}).dialing_code;
            },
            fetchStores() {
                this.parentFetchStores();
            }
        }
    };

</script>
