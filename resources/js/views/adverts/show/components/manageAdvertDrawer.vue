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

            <advertManager
                :advert="advert" :adverts="adverts" @createdAdvert="handleCreatedAdvert($event)"
                @savedAdvert="handleSavedAdvert($event)" @fetchedAdvert="handleFetchedAdvert($event)"
                @closeDrawer="closeDrawer()">
            </advertManager>

        </Drawer>

    </div>
</template>
<script>

    import advertManager from './../main.vue';
    import drawerMixin from './../../../../components/_mixins/drawer/main.vue';

    export default {
        mixins: [ drawerMixin ],
        components: { advertManager },
        props: {
            advert: {
                type: Object,
                default: null
            },
            adverts: {
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
                //  If we have a advert then use "Save Advert" otherwise "Create Advert" as the ok text
                return this.advert ? 'Save Advert' : 'Create Advert';
            },
            drawerTitle(){

                if( this.advert ){

                        return 'Edit Advert';

                }else{

                    return 'Create Advert';

                }
            }
        },
        methods: {
            handleCreatedAdvert(advert){

                //  Notify parent of the advert created
                this.$emit('createdAdvert', advert);

            },
            handleSavedAdvert(advert){

                //  Notify parent of the advert saved
                this.$emit('savedAdvert', advert);

            },
            handleFetchedAdvert(advert){

                //  Notify parent of the advert fetched
                this.$emit('fetchedAdvert', advert);

            },
        }
    }
</script>
