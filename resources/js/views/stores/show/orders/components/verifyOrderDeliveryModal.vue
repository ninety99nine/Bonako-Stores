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
            title="Verify Order Delivery"
            @on-visible-change="detectClose">

            <!-- Error Message Alert -->
            <Alert v-if="serverErrorMessage && !isResending && !isVerifying" type="warning">{{ serverErrorMessage }}</Alert>

            <template>

                <h1 :class="['mb-2', 'text-secondary']" :style="{ fontSize: '20px' }">Order #{{ order.number }}</h1>

                <!-- Instructions Alert -->
                <Alert type="warning" :style="{ lineHeight: '1.5em' }" class="p-2">
                    <span :style="{ display: 'block', textAlign: 'justify' }">Please enter the order <span class="text-primary">delivery confirmation code</span> to verify that the order has been delivered to the customer. By entering the delivery confirmation code, you are verifying that the customer order has been paid and delivered to the customer successfully.</span>
                    <Poptip trigger="hover" width="400" class="mt-2" word-wrap>
                        <span slot="content">Hi {{ user.first_name }}, we sent a <span class="text-primary">6 digit delivery confirmation code</span> to your customer <span class="text-primary">{{ deliveryLineName }}</span> on their mobile number <span class="text-primary">{{ deliveryLineMobileNumber }}</span>. Please ask your customer to provide you with the code then enter the code to verify that you delivered the order to your customer.</span>
                        <Icon type="ios-information-circle-outline" :size="20" />
                        <span :class="['text-primary', 'border-primary', 'border-bottom-dashed']">How to get code?</span>
                    </Poptip>
                </Alert>

                <div class="my-4">

                    <!-- If we are loading, Show Loader -->
                    <Loader v-if="isResending || isVerifying" class="mt-2">
                        {{ isResending ? 'Sending...' : 'Verifying...' }}
                    </Loader>

                    <Form v-else ref="loginForm">

                        <FormItem prop="delivery_confirmation_code" :error="serverDeliveryConfirmationCodeError" class="text-center">

                            <!-- 6 Digit Verification Input -->
                            <CodeInput :loading="isVerifying" class="input m-auto" v-on:change="onChange" :key="renderKey"/>

                        </FormItem>

                    </Form>

                </div>

            </template>

            <!-- Footer -->
            <template v-slot:footer>
                <div v-if="!isResending" class="clearfix">
                    <Button type="default" class="float-left" :disabled="isVerifying"
                            @click.native="resendDeliveryConfirmationCode()">
                        Resend Confirmation Code
                    </Button>
                    <Button type="success" class="float-right" :disabled="isVerifying || !canVerify"
                            @click.native="verifyDeliveryConfirmationCode()">
                        Verify
                    </Button>
                    <Button @click.native="closeModal()" class="float-right mr-2" :disabled="isVerifying">Cancel</Button>
                </div>
            </template>

        </Modal>
    </div>
</template>
<script>

    import CodeInput from "vue-verification-code-input";
    import Loader from './../../../../../components/_common/loaders/default.vue';
    import modalMixin from './../../../../../components/_mixins/modal/main.vue';
    var customMixin = require('./../../../../../mixin.js').default;

    export default {
        mixins: [modalMixin, customMixin],
        components: { CodeInput, Loader },
        props: {
            order: {
                type: Object,
                default: null
            }
        },
        data(){
            return {
                user: auth.getUser(),
                isVerifying: false,
                isResending: false,
                renderKey: 1,
                code: null,
                serverErrors: [],
                serverErrorMessage: null,
            }
        },
        computed: {
            deliveryLine(){
                return (((this.order || {})._embedded || {}).delivery_line || {});
            },
            deliveryLineName(){
                return this.deliveryLine.name
            },
            deliveryLineMobileNumber(){
                return this.deliveryLine.mobile_number
            },
            fulfilOrderUrl(){
                return (this.order || {})['_links']['bos:fulfil'].href;
            },
            canVerify(){
                return ((this.code || {}).length == 6);
            },
            serverDeliveryConfirmationCodeError(){
                return (this.serverErrors || {}).delivery_confirmation_code;
            },
        },
        methods: {
            onChange(code) {

                this.code = code;

                //  Reset the server errors
                this.resetErrors();
            },
            resendDeliveryConfirmationCode(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isResending = true;

                /** Attempt to send the mobile account verification code using the auth
                 *  sendMobileAccountVerificationCode method found in the auth.js file
                 */
                auth.sendMobileAccountVerificationCode(this.mobileNumber)
                    .then(({data}) => {

                        //  Stop loader
                        self.isResending = false;


                    }).catch((response) => {

                        //  Stop loader
                        self.handleApiFail(response);

                    });
            },
            verifyDeliveryConfirmationCode(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isVerifying = true;

                var fulfilData = {
                    delivery_confirmation_code: this.code
                }

                //  Use the api call() function, refer to api.js
                api.call('put', this.fulfilOrderUrl, fulfilData)
                    .then(({data}) => {

                    //  Stop loader
                    self.isVerifying = false;

                    self.$Message.success({
                        content: 'Order Fulfilled!',
                        duration: 6
                    });

                    self.$emit('verified', data);

                    /** Note the closeModal() method is imported from the
                     *  modalMixin file. It handles the closing process
                     *  of the modal
                     */
                    self.closeModal();

                }).catch((response) => {

                    //  Stop loader
                    self.handleApiFail(response);

                });
            },
            handleApiFail(response){

                console.error(response);

                //  Stop loader
                this.isResending = false;

                //  Stop loader
                this.isVerifying = false;

                //  Get the error response data
                let data = (response || {}).data;

                //  Get the response errors
                var errors = (data || {}).errors;

                /**
                 *  422: Validation failed. Incorrect credentials
                 */
                if((response || {}).status === 422){

                    //  If we have errors
                    if(_.size(errors)){

                        //  Set the server errors
                        this.serverErrors = errors;

                        //  Foreach error
                        for (var i = 0; i < _.size(errors); i++) {
                            //  Get the error key e.g 'email', 'password'
                            var prop = Object.keys(errors)[i];
                            //  Get the error value e.g 'These credentials do not match our records.'
                            var value = Object.values(errors)[i][0];

                            //  Dynamically update the serverErrors for View UI to display the error on the appropriate form item
                            this.serverErrors[prop] = value;
                        }

                    }

                }else{

                    //  Set the general error message
                    this.serverErrorMessage = (data || {}).message;

                }

            },
            resetErrors(){
                this.serverErrorMessage = '';
                this.serverErrors = [];
            }
        }
    }
</script>
