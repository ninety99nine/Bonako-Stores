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

            <couponManager
                :store="store" :location="location" :assignedLocations="assignedLocations" :layoutSize="layoutSize"
                :coupon="coupon" :coupons="coupons" @createdCoupon="handleCreatedCoupon($event)"
                @savedCoupon="handleSavedCoupon($event)" @fetchedCoupon="handleFetchedCoupon($event)"
                @closeDrawer="closeDrawer()">
            </couponManager>

        </Drawer>

    </div>
</template>
<script>

    import couponManager from '../show/main.vue';
    import drawerMixin from '../../../../../components/_mixins/drawer/main.vue';

    export default {
        mixins: [ drawerMixin ],
        components: { couponManager },
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
            coupon: {
                type: Object,
                default: null
            },
            coupons: {
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
                //  If we have a coupon then use "Save Coupon" otherwise "Create Coupon" as the ok text
                return this.coupon ? 'Save Coupon' : 'Create Coupon';
            },
            drawerTitle(){

                if( this.coupon ){

                        return 'Edit Coupon';

                }else{

                    return 'Create Coupon';

                }
            }
        },
        methods: {
            handleCreatedCoupon(coupon){

                //  Notify parent of the coupon created
                this.$emit('createdCoupon', coupon);

            },
            handleSavedCoupon(coupon){

                //  Notify parent of the coupon saved
                this.$emit('savedCoupon', coupon);

            },
            handleFetchedCoupon(coupon){

                //  Notify parent of the coupon fetched
                this.$emit('fetchedCoupon', coupon);

            },
        }
    }
</script>
