<template>
    <div>
        <!-- Modal

             Note: drawerVisible and detectClose() are imported from the drawerMixin.
             They are used to allow for opening and closing the drawer properly
             during the v-if conditional statement of the parent component. It
             is important to note that <Modal> does not open/close well with
             v-if statements by default, therefore we need to add additional
             functionality to enhance the experience. Refer to drawerMixin.
        -->
        <Drawer
            width="650"
            :okText="okText"
            cancelText="Cancel"
            :title="drawerTitle"
            :maskClosable="false"
            @on-ok="closeDrawer()"
            v-model="drawerVisible"
            @on-visible-change="detectClose">

            <popularStoreManager
                :popularStore="popularStore" :popularStores="popularStores" @createdPopularStore="handleCreatedPopularStore($event)"
                @savedPopularStore="handleSavedPopularStore($event)" @fetchedPopularStore="handleFetchedPopularStore($event)"
                @closeDrawer="closeDrawer()">
            </popularStoreManager>

        </Drawer>

    </div>
</template>
<script>

    import popularStoreManager from './../main.vue';
    import drawerMixin from './../../../../components/_mixins/drawer/main.vue';

    export default {
        mixins: [ drawerMixin ],
        components: { popularStoreManager },
        props: {
            popularStore: {
                type: Object,
                default: null
            },
            popularStores: {
                type: Array,
                default: function(){
                    return [];
                }
            }
        },
        data(){
            return{

            }
        },
        computed: {
            okText(){
                //  If we have a popular store then use "Save Popular Store" otherwise "Create Popular Store" as the ok text
                return this.popularStore ? 'Save Popular Store' : 'Create Popular Store';
            },
            drawerTitle(){

                if( this.popularStore ){

                        return 'Edit Popular Store';

                }else{

                    return 'Create Popular Store';

                }
            }
        },
        methods: {
            handleCreatedPopularStore(popularStore){

                //  Notify parent of the popular store created
                this.$emit('createdPopularStore', popularStore);

            },
            handleSavedPopularStore(popularStore){

                //  Notify parent of the popular store saved
                this.$emit('savedPopularStore', popularStore);

            },
            handleFetchedPopularStore(popularStore){

                //  Notify parent of the popular store fetched
                this.$emit('fetchedPopularStore', popularStore);

            },
        }
    }
</script>
