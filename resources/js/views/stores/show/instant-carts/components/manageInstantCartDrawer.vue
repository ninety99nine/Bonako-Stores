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
            :mask="mask"
            :okText="okText"
            cancelText="Cancel"
            :title="drawerTitle"
            :maskClosable="false"
            @on-ok="closeDrawer()"
            v-model="drawerVisible"
            @on-visible-change="detectClose">

            <instantCartManager
                :store="store" :location="location" :assignedLocations="assignedLocations" :layoutSize="layoutSize"
                :instantCart="instantCart" :instantCarts="instantCarts" @createdInstantCart="handleCreatedInstantCart($event)"
                @savedInstantCart="handleSavedInstantCart($event)" @fetchedInstantCart="handleFetchedInstantCart($event)"
                @closeDrawer="closeDrawer()">
            </instantCartManager>

        </Drawer>

    </div>
</template>
<script>

    import instantCartManager from './../show/main.vue';
    import drawerMixin from './../../../../../components/_mixins/drawer/main.vue';

    export default {
        mixins: [ drawerMixin ],
        components: { instantCartManager },
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
            assignedLocations: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            instantCart: {
                type: Object,
                default: null
            },
            instantCarts: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            layoutSize: {
                type: String
            },
            mask: {
                type: Boolean,
                default: true
            },
        },
        data(){
            return{

            }
        },
        computed: {
            okText(){
                //  If we have a instant cart then use "Save Instant Cart" otherwise "Create Instant Cart" as the ok text
                return this.instantCart ? 'Save Instant Cart' : 'Create Instant Cart';
            },
            drawerTitle(){

                if( this.instantCart ){

                        return 'Edit Instant Cart';

                }else{

                    return 'Create Instant Cart';

                }
            }
        },
        methods: {
            handleCreatedInstantCart(instantCart){

                //  Notify parent of the instant cart created
                this.$emit('createdInstantCart', instantCart);

            },
            handleSavedInstantCart(instantCart){

                //  Notify parent of the instant cart saved
                this.$emit('savedInstantCart', instantCart);

            },
            handleFetchedInstantCart(instantCart){

                //  Notify parent of the instant cart fetched
                this.$emit('fetchedInstantCart', instantCart);

            },
        }
    }
</script>
