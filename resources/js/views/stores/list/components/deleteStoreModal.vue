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
            title="Delete Store"
            v-model="modalVisible"
            @on-visible-change="detectClose">

            <!-- If we are deleting, Show Loader -->
            <Loader v-if="isDeleting" class="mt-2">Deleting store...</Loader>

            <!-- Form -->
            <Form v-else ref="storeForm" :model="storeForm" :rules="storeFormRules">

                <Alert type="warning">
                    Delete <span class="font-weight-bold">{{ store.name }}</span>
                    <Divider class="my-2" />
                    <template slot="desc">
                        Please enter the store name "{{ store.name }}" into the input field below. This
                        confirms that you agree to delete this store permanetly. After deleting this
                        store cannot be recovered again.
                    </template>
                </Alert>

                <!-- Enter Name -->
                <FormItem prop="name">
                    <Input  type="text" v-model="storeForm.name" :placeholder="store.name" maxlength="50"
                            show-word-limit @keyup.enter.native="handleSubmit()" v-focus="'input'">
                            <span slot="prepend">Name</span>
                    </Input>
                </FormItem>

            </Form>

            <!-- Footer -->
            <template v-slot:footer>
                <div class="clearfix">
                    <Button type="error" @click.native="handleSubmit()" class="float-right" :disabled="isDeleting">Delete Store</Button>
                    <Button @click.native="closeModal()" class="float-right mr-2" :disabled="isDeleting">Cancel</Button>
                </div>
            </template>

        </Modal>
    </div>
</template>
<script>

    import Loader from './../../../../components/_common/loaders/default.vue';
    import modalMixin from './../../../../components/_mixins/modal/main.vue';
    import miscMixin from './../../../../components/_mixins/misc/main.vue';

    export default {
        mixins: [modalMixin, miscMixin],
        components: { Loader },
        props: {
            index: {
                type: Number,
                default: null
            },
            store: {
                type: Object,
                default: null
            },
            stores: {
                type: Array,
                default:() => []
            }
        },
        data(){

            //  Custom validation to approve deletion
            const deleteValidator = (rule, value, callback) => {

                //  If the provided store name matches the actual store name
                if (this.storeForm.name == this.store.name) {
                    callback();
                } else {
                    callback(new Error('Sorry, the store name does not match'));
                }
            };

            return {
                storeForm: null,
                isDeleting: false,
                storeFormRules: {
                    name: [
                        { required: true, message: 'Please enter your store name', trigger: 'blur' },
                        { validator: deleteValidator, trigger: 'change' }
                    ],
                }
            }
        },
        methods: {

            getStoreForm(){

                //  Set the default form details
                return {
                    name: ''
                }

            },
            handleSubmit(){

                //  Validate the store form
                this.$refs['storeForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  Delete the store
                        this.attemptStoreDeletion();

                    //  If the validation failed
                    } else {
                        this.$Message.warning({
                            content: 'Sorry, you cannot delete your store yet',
                            duration: 6
                        });
                    }
                })

            },
            attemptStoreDeletion(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isDeleting = true;

                return api.call('delete', this.store['_links']['self'].href)
                    .then(({data}) => {

                        //  Stop loader
                        self.isDeleting = false;

                        self.$emit('deleted');

                        //  Reset the store
                        self.removeStore();

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
            removeStore(){

                //  Remove store from list
                this.stores.splice(this.index, 1);

                //  Store deleted success message
                this.$Message.success({
                    content: 'Store deleted!',
                    duration: 6
                });
            }
        },
        created(){
            this.storeForm = this.getStoreForm();
        }
    }
</script>
