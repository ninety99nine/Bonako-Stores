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
            title="Delete Product"
            v-model="modalVisible"
            @on-visible-change="detectClose">

            <!-- If we are deleting, Show Loader -->
            <Loader v-if="isDeleting" class="mt-2">Deleting product...</Loader>

            <!-- Disclaimer -->
            <Alert v-else type="warning">
                Delete <span class="font-weight-bold">{{ product.name }}</span>
                <Divider class="my-2" />
                <template slot="desc">
                    This product will be deleted permanently cannot be recovered again.
                </template>
            </Alert>

            <!-- Footer -->
            <template v-slot:footer>
                <div class="clearfix">
                    <Button type="error" @click.native="attemptProductDeletion()" class="float-right" :disabled="isDeleting">Delete Product</Button>
                    <Button @click.native="closeModal()" class="float-right mr-2" :disabled="isDeleting">Cancel</Button>
                </div>
            </template>

        </Modal>
    </div>
</template>
<script>

    import Loader from './../../../../../components/_common/loaders/default.vue';
    import modalMixin from './../../../../../components/_mixins/modal/main.vue';

    export default {
        mixins: [modalMixin],
        components: { Loader },
        props: {
            index: {
                type: Number,
                default: null
            },
            product: {
                type: Object,
                default: null
            },
            products: {
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
            productUrl(){
                return this.product['_links']['self'].href;
            }
        },
        methods: {
            attemptProductDeletion(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isDeleting = true;

                return api.call('delete', this.productUrl)
                    .then(({data}) => {

                        //  Stop loader
                        self.isDeleting = false;

                        //  Reset the product
                        self.removeProduct();

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
            removeProduct(){

                //  Remove product from list
                this.products.splice(this.index, 1);

                //  Product deleted success message
                this.$Message.success({
                    content: 'Product deleted!',
                    duration: 6
                });
            }
        }
    }
</script>
