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
            v-model="modalVisible"
            @on-visible-change="detectClose">

            <div class="mt-4">

                <!-- If we are deleting, Show Loader -->
                <Loader v-if="isDeleting" class="mt-2">Removing {{ advert.name }}...</Loader>

                <!-- Disclaimer -->
                <Alert v-else type="warning">
                    Remove <span class="font-weight-bold">{{ advert.name }}</span>
                    <Divider class="my-2" />
                    <template slot="desc">
                        This advert will be removed permanently and cannot be recovered again.
                    </template>
                </Alert>

            </div>

            <!-- Footer -->
            <template v-slot:footer>
                <div class="clearfix">
                    <Button type="error" @click.native="attemptAdvertDeletion()" class="float-right" :disabled="isDeleting">Remove</Button>
                    <Button @click.native="closeModal()" class="float-right mr-2" :disabled="isDeleting">Cancel</Button>
                </div>
            </template>

        </Modal>
    </div>
</template>
<script>

    import Loader from './../../../../components/_common/loaders/default.vue';
    import modalMixin from './../../../../components/_mixins/modal/main.vue';

    export default {
        mixins: [modalMixin],
        components: { Loader },
        props: {
            index: {
                type: Number,
                default: null
            },
            advert: {
                type: Object,
                default: null
            },
            adverts: {
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
            advertUrl(){
                return this.advert['_links']['self'].href;
            }
        },
        methods: {
            attemptAdvertDeletion(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isDeleting = true;

                return api.call('delete', this.advertUrl)
                    .then(({data}) => {

                        //  Stop loader
                        self.isDeleting = false;

                        //  Remove the advert
                        self.removeAdvert();

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
            removeAdvert(){

                //  Remove advert from list
                this.adverts.splice(this.index, 1);

                //  Advert removed success message
                this.$Message.success({
                    content: 'Advert removed!',
                    duration: 6
                });
            }
        }
    }
</script>
