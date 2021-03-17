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
            width="600"
            v-model="modalVisible"
            title="Manage Quantity"
            @on-visible-change="detectClose">

            <!-- Form -->
            <Form ref="instantCartForm" :model="instantCartForm">

                <Row :gutter="10" class="mb-2">

                    <Col :span="12">

                        <span class="font-weight-bold">Product Name</span>

                    </Col>

                    <Col :span="12">

                        <span class="font-weight-bold">Quantity</span>

                    </Col>

                </Row>

                <Row :gutter="12" v-for="(item, index) in instantCartForm.items" :key="index" class="mb-2">

                    <Col :span="12">

                        <span>{{ item.name }}</span>

                    </Col>

                    <Col :span="12">

                        <!-- Enter Quantity -->
                        <InputNumber v-model.number="item.quantity" class="w-100" placeholder="40" :min="1"
                                    @keyup.enter.native="handleSubmit()">
                        </InputNumber>

                    </Col>

                </Row>

            </Form>

            <!-- Footer -->
            <template v-slot:footer>
                <div class="clearfix">
                    <Button type="primary" @click.native="handleSubmit()" class="float-right">Done</Button>
                    <Button @click.native="closeModal()" class="float-right mr-2">Cancel</Button>
                </div>
            </template>

        </Modal>
    </div>
</template>
<script>


    import basicButton from './../../../../../../components/_common/buttons/basicButton.vue';
    import modalMixin from './../../../../../../components/_mixins/modal/main.vue';

    export default {
        mixins: [ modalMixin ],
        components: { basicButton },
        props: {
            instantCart: {
                type: Object,
                default: null
            },
        },
        data(){
            return {
                instantCartForm: null
            }
        },
        computed: {

        },
        methods: {
            getInstantCartForm(){

                var instantCart = Object.assign({}, this.instantCart);

                return _.cloneDeep( instantCart );

            },
            handleConfirmRemoveProduct(index){

                const self = this;

                var name = this.instantCartForm.items[index].name;

                //  Make a popup confirmation modal so that we confirm the destination removal
                this.$Modal.confirm({
                    width: '450',
                    closable: true,
                    okText: 'Delete',
                    cancelText: 'Cancel',
                    title: 'Delete Destination',
                    onOk: () => { this.handleRemoveProduct(index) },
                    render: function (h) {
                        return h(
                            'span', [
                                'Are you sure you want to remove "',
                                h('span', { class: ['font-weight-bold'] }, name)
                            ]
                        )
                    }
                });
            },
            handleRemoveProduct(index) {

                //  Remove product from list
                this.instantCartForm.items.splice(index, 1);

                //  Product removed success message
                this.$Message.success({
                    content: 'Cart product removed!',
                    duration: 6
                });
            },
            handleSubmit(){

                var items = this.instantCartForm.items.map((product) => {

                    if( product.quantity == null || product.quantity == undefined || product.quantity == 0){

                        //  Reset quantity to 1
                        product.quantity = 1;

                    }

                    return product;

                });

                //  Update the instant cart items
                this.$set(this.instantCart, 'items', items);

                this.$Message.success({
                    content: 'Instant cart products updated!',
                    duration: 6
                });

                //  Notify the parent component of the updated cart items
                this.$emit('updated');

                /** Note the closeModal() method is imported from the
                 *  modalMixin file. It handles the closing process
                 *  of the modal
                 */
                this.closeModal();

            }
        },
        created(){
            this.instantCartForm = this.getInstantCartForm();
        }
    }
</script>
