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
            title="Verify Mobile Number"
            @on-visible-change="detectClose">

            <!-- If we are loading, Show Loader -->
            <Loader v-if="isSending || isVerifying" class="mt-2">
                {{ isSending ? 'One moment...' : 'Signing in...' }}
            </Loader>

            <!-- Error Message Alert -->
            <Alert v-if="serverErrorMessage && !isSending && !isVerifying" type="warning">{{ serverErrorMessage }}</Alert>

            <template v-if="!isSending && !serverErrorMessage">

                <!-- Error Message Alert -->
                <Alert type="info" class="p-2">
                    Hi {{ userAccount.first_name }}, we sent a <span class="text-primary">6 digit code</span>
                    to the mobile number <span class="text-primary">{{ mobileNumber }}</span>. Please enter
                    the code below so that we know that you are the owner of this mobile number.
                </Alert>

                <div class="my-4">

                    <!-- 6 Digit Verification Input -->
                    <CodeInput :loading="isVerifying" class="input m-auto" v-on:change="onChange" :key="renderKey"/>

                </div>

            </template>

            <!-- Footer -->
            <template v-slot:footer>
                <div v-if="!isSending" class="clearfix">
                    <Button type="success" class="float-right" :disabled="isVerifying || !canVerify"
                            @click.native="attemptVerifyMobileAccountVerificationCode()">
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
    import Loader from './../../../components/_common/loaders/default.vue';
    import modalMixin from './../../../components/_mixins/modal/main.vue';
    var customMixin = require('./../../../mixin.js').default;

    export default {
        mixins: [modalMixin, customMixin],
        components: { CodeInput, Loader },
        props: {
            userAccount: {
                type: Object,
                default: null
            },
            mobileNumber: {
                type: String,
                default: null
            },
        },
        data(){
            return {
                serverErrorMessage: null,
                isVerifying: false,
                isSending: false,
                renderKey: 1,
                code: null
            }
        },
        computed: {
            canVerify(){
                return ((this.code || {}).length == 6);
            }
        },
        methods: {
            onChange(code) {
                this.code = code;
            },
            attemptSendMobileAccountVerificationCode(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isSending = true;

                /** Attempt to send the mobile account verification code using the auth
                 *  sendMobileAccountVerificationCode method found in the auth.js file
                 */
                auth.sendMobileAccountVerificationCode(this.mobileNumber)
                    .then(({data}) => {

                        //  Stop loader
                        self.isSending = false;


                    }).catch((response) => {

                        //  Stop loader
                        self.handleApiFail(response);

                    });
            },
            attemptVerifyMobileAccountVerificationCode(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isVerifying = true;

                /** Attempt to send the mobile account verification code using the auth
                 *  verifyMobileAccountVerificationCode method found in the auth.js file
                 */
                auth.verifyMobileAccountVerificationCode(this.mobileNumber, this.code)
                    .then(({data}) => {

                        //  Stop loader
                        self.isVerifying = false;

                        //  If we have a matching verification code
                        if( (data || {}).status === true ){

                            this.$Message.success({
                                content: 'Mobile number verified!',
                                duration: 6
                            });

                            self.$emit('verified', self.code);

                            /** Note the closeModal() method is imported from the
                             *  modalMixin file. It handles the closing process
                             *  of the modal
                             */
                            self.closeModal();

                        }else{

                            this.$Message.warning({
                                content: 'Incorrect code provided, try again',
                                duration: 6
                            });

                            //  Reset the code
                            self.code = null;

                            //  Reset the input field
                            ++self.renderKey;

                        }


                    }).catch((response) => {

                        //  Stop loader
                        self.handleApiFail(response);

                    });
            },
            handleApiFail(response){

                console.error(response);

                //  Stop loader
                this.isSending = false;

                //  Stop loader
                this.isVerifying = false;

                //  Get the error response data
                let data = (response || {}).data;

                //  Get the response errors
                var errors = (data || {}).errors;

                //  Set the general error message
                this.serverErrorMessage = (data || {}).message;

            }
        },
        created(){

            //  Send a new mobile account verification code via SMS
            this.attemptSendMobileAccountVerificationCode();

        }
    }
</script>
