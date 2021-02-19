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
            title="Payment Request"
            @on-visible-change="detectClose">

            <h1 :class="['mb-2', 'text-secondary']" :style="{ fontSize: '20px' }">Order #{{ order.number }}</h1>

            <!-- Customer -->
            <Card v-if="customer" class="cursor-pointer mb-2">

                <span :class="['font-weight-bold', 'd-block', 'mb-3']"
                        :style="{ fontSize: 'large', lineHeight: 'initial' }">
                    Customer
                </span>

                <div :class="['align-items-center', 'd-flex']">
                    <Avatar icon="ios-person" :style="{ background: '#19be6b' }" class="mr-2" />
                    <p class="mr-2">{{ customerName }}</p>
                    <p>
                        <Icon type="ios-call-outline" class="mr-1" :size="20" />
                        <span>{{ customerMobileNumber }}</span>
                    </p>
                </div>

            </Card>

            <!-- Instructions Alert -->
            <Alert v-if="customer" type="warning" :style="{ lineHeight: '1.5em' }" class="p-2">
                <span :style="{ display: 'block', textAlign: 'justify' }"><span :class="['font-weight-bold']">Disclaimer: </span>By sending the <span class="text-primary">Payment Request</span>, you are verifying that the customer has been informed and notified on the purpose of the payment shortcode for use to pay for their order.</span>
            </Alert>

            <!-- If we don't have a customer -->
            <Alert v-if="!customer" type="warning" class="mx-5" show-icon>
                <span :class="['font-weight-bold']">Customer Not Found</span>
                <div :class="['mt-2']">
                    This order does not have a customer assigned, therefore we cannot generate a payment shortcode.
                </div>
            </Alert>

            <!-- Footer -->
            <template v-slot:footer>
                <div class="clearfix">
                    <Button type="success" class="float-right" :disabled="isLoading" :loading="isLoading"
                            @click.native="sendPaymentRequest()">
                        Send Payment Request
                    </Button>
                    <Button @click.native="closeModal()" class="float-right mr-2" :disabled="isLoading">Cancel</Button>
                </div>
            </template>
        </Modal>
    </div>
</template>
<script>

    import Loader from './../../../../../components/_common/loaders/default.vue';
    import modalMixin from './../../../../../components/_mixins/modal/main.vue';
    var customMixin = require('./../../../../../mixin.js').default;

    export default {
        mixins: [modalMixin, customMixin],
        components: { Loader },
        props: {
            order: {
                type: Object,
                default: null
            }
        },
        data(){
            return {
                isLoading: false
            }
        },
        computed: {
            customer(){
                return (this.order._embedded.customer || {});
            },
            customerName(){
                return this.customer._attributes.name;
            },
            customerMobileNumber(){
                return this.customer.mobile_number;
            },
            paymentStatus(){
                return (this.order._embedded.payment_status || {});
            },
            isPendingPayment(){
                return (this.paymentStatus.name == 'Pending') ? true : false;
            },
            paymentRequestUrl(){
                return this.order['_links']['bos:payment_request']['href'];
            },
            hasPaymentShortCode(){
                return this.order['_attributes']['payment_short_code'] ? true : false;
            }
        },
        methods: {
            sendPaymentRequest(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                //  Use the api call() function, refer to api.js
                api.call('post', this.paymentRequestUrl)
                    .then(({data}) => {

                    //  Stop loader
                    self.isLoading = false;

                    self.$Message.success({
                        content: 'Payment Request Sent!',
                        duration: 6
                    });

                    self.$emit('sentPaymentRequest', data);

                    /** Note the closeModal() method is imported from the
                     *  modalMixin file. It handles the closing process
                     *  of the modal
                     */
                    self.closeModal();

                }).catch((response) => {

                    console.error(response);

                    //  Stop loader
                    this.isLoading = false;

                });
            }
        }
    }
</script>
