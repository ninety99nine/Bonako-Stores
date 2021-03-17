<template>
    <div>
        <!-- Modal

             Note: modalVisible and detectClose() are imported from the modalMixin.
             They are used to allow for opening and closing the modal properly
             during the v-if conditional statement of the parent component. It
             is important to note that <Modal> does not open/close well with
             v-if statements by default, therefore we need to add additional
             functionality to enhance the experience. Refer to modalMixin.
        -->
        <Modal
            title="Delete Coupon"
            v-model="modalVisible"
            @on-visible-change="detectClose">

            <!-- If we are deleting, Show Loader -->
            <Loader v-if="isDeleting" class="mt-2">Deleting coupon...</Loader>

            <!-- Disclaimer -->
            <Alert v-else type="warning">
                Delete <span class="font-weight-bold">{{ coupon.name }}</span>
                <Divider class="my-2" />
                <template slot="desc">
                    This coupon will be deleted permanently cannot be recovered again.
                </template>
            </Alert>

            <!-- Footer -->
            <template v-slot:footer>
                <div class="clearfix">
                    <Button type="error" @click.native="attemptCouponDeletion()" class="float-right" :disabled="isDeleting">Delete</Button>
                    <Button @click.native="closeModal()" class="float-right mr-2" :disabled="isDeleting">Cancel</Button>
                </div>
            </template>

        </Modal>
    </div>
</template>
<script>

    import Loader from '../../../../../components/_common/loaders/default.vue';
    import modalMixin from '../../../../../components/_mixins/modal/main.vue';

    export default {
        mixins: [modalMixin],
        components: { Loader },
        props: {
            index: {
                type: Number,
                default: null
            },
            coupon: {
                type: Object,
                default: null
            },
            coupons: {
                type: Array,
                default:() => []
            }
        },
        data(){

            return {
                isDeleting: false
            }
        },
        computed: {
            couponUrl(){
                return this.coupon['_links']['self'].href;
            }
        },
        methods: {
            attemptCouponDeletion(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isDeleting = true;

                return api.call('delete', this.couponUrl)
                    .then(({data}) => {

                        //  Stop loader
                        self.isDeleting = false;

                        //  Reset the coupon
                        self.removeCoupon();

                        //  Notify parent
                        self.$emit('deleted');

                        /** Note the closeModal() method is imported from the
                         *  modalMixin file. It handles the closing process
                         *  of the modal
                         */
                        self.closeModal();

                    }).catch((response) => {

                        //  Stop loader
                        self.isDeleting = false;

                });
            },
            removeCoupon(){

                //  Remove coupon from list
                this.coupons.splice(this.index, 1);

                //  Coupon deleted success message
                this.$Message.success({
                    content: 'Coupon deleted!',
                    duration: 6
                });
            }
        }
    }
</script>
