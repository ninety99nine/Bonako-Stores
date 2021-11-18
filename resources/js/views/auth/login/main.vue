<template>

    <Row>
        <Col span="8" :offset="8">
            <Card class="auth-form mt-5 pt-2">

                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold">Sign In</Divider>

                <!-- Error Message Alert -->
                <Alert v-if="serverErrorMessage && !isLoading" type="warning">{{ serverErrorMessage }}</Alert>

                <Form ref="loginForm" :model="loginForm" :rules="loginFormRules">

                    <Tabs v-show="step == 1" :animated="false" v-model="activeTab">

                        <TabPane :label="mobileTabLabel" class="pt-3" name="mobileTab">

                            <div v-if="activeTab == 'mobileTab'">

                                <!-- Enter Mobile Number -->
                                <FormItem prop="mobile_number" :error="serverMobileNumberError">
                                    <Input type="text" v-model="loginForm.mobile_number" placeholder="Mobile Number"
                                        :disabled="isLoading" size="large" @keyup.enter.native="handleSubmit()">
                                        <Icon type="ios-phone-portrait" slot="prepend"></Icon>
                                    </Input>
                                </FormItem>

                            </div>

                        </TabPane>

                        <TabPane :label="emailTabLabel" class="pt-3" name="emailTab">

                            <div v-if="activeTab == 'emailTab'">

                                <!-- Enter Email -->
                                <FormItem prop="email" :error="serverEmailError">
                                    <Input type="email" v-model="loginForm.email" placeholder="Email" :disabled="isLoading"
                                           size="large" @keyup.enter.native="handleSubmit()">
                                        <Icon type="ios-mail-outline" slot="prepend"></Icon>
                                    </Input>
                                </FormItem>

                            </div>

                        </TabPane>

                    </Tabs>

                    <div v-if="step == 2">

                        <Card v-if="userAccount" class="cursor-pointer mb-2" @click.native="previousStep()">
                            <Avatar icon="ios-person" :style="{ background: '#19be6b' }" class="mr-1" />
                            <span>{{ userAccount.first_name }} {{ userAccount.last_name }}</span>
                        </Card>

                        <!-- Error Message Alert -->
                        <Alert v-if="requiresPassword" type="info">
                            Hi {{ userAccount.first_name }}, your account does not have a password. Please enter a new password to login into your account.
                        </Alert>

                        <!-- Enter Password -->
                        <FormItem prop="password" :error="serverPasswordError" class="mb-2">
                            <Input :type="showPassword ? 'text' : 'password'" v-model="loginForm.password" placeholder="Password"
                                    size="large" :disabled="isLoading" @keyup.enter.native="handleSubmit()">
                                <Icon type="ios-lock-outline" slot="prepend"></Icon>
                            </Input>
                        </FormItem>

                        <!-- Confirm Password -->
                        <FormItem v-if="requiresPassword" prop="password_confirmation" class="mb-2">
                            <Input :type="showPassword ? 'text' : 'password'" v-model="loginForm.password_confirmation"
                                    size="large" placeholder="Confirm Password" :disabled="isLoading"
                                    @keyup.enter.native="handleSubmit()">
                                <Icon type="ios-lock-outline" slot="prepend"></Icon>
                            </Input>
                        </FormItem>

                        <Checkbox v-model="showPassword" class="my-4">Show password</Checkbox>

                    </div>

                    <!-- Register Link -->
                    <router-link to="register" class="mr-2">Create Account?</router-link>

                    <!-- Forgot Password Link -->
                    <router-link to="forgot-password">Forgot Password?</router-link>

                    <!-- Sign In Button -->
                    <FormItem v-if="!isLoading">
                        <div class="clearfix">
                            <Button v-if="step == 1" type="primary" class="float-right" :disabled="isLoading" @click="nextStep()">Next</Button>
                            <Button v-if="step == 2" type="primary" class="float-right" :disabled="isLoading" @click="handleSubmit()">Sign In</Button>
                            <Button v-if="step == 2" type="default" class="float-right mr-2" :disabled="isLoading" @click="previousStep()">
                                <Icon type="md-arrow-round-back" />
                                <span>Back</span>
                            </Button>
                        </div>
                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isLoading" class="mt-2">
                        <span v-if="step == 1">Loading account...</span>
                        <span v-if="step == 2">Signing in...</span>
                    </Loader>

                </Form>
            </Card>

            <!--
                MODAL TO VERIFY MOBILE NUMBER
            -->
            <template v-if="isOpenVerifyMobileNumberModal">

                <verifyMobileNumberModal
                    :userAccount="userAccount"
                    @verified="handleVerifiedMobileNumber"
                    :mobileNumber="loginForm.mobile_number"
                    @visibility="isOpenVerifyMobileNumberModal = $event">
                </verifyMobileNumberModal>

            </template>

        </Col>
    </Row>

</template>
<script>


    import miscMixin from './../../../components/_mixins/misc/main.vue';
    import verifyMobileNumberModal from './verifyMobileNumberModal.vue';
    import Loader from './../../../components/_common/loaders/default.vue';

    export default {
        mixins: [miscMixin],
        components: { verifyMobileNumberModal, Loader },
        data () {

            //  Custom validation to detect non matching passwords
            const samePasswordValidator = (rule, value, callback) => {
                //  If the confirm password does not match the password
                if (value !== this.loginForm.password) {
                    callback(new Error('The passwords provided do not match!'));
                } else {
                    callback();
                }
            };

            return {
                isLoading: false,
                loginForm: {
                    email: '',
                    mobile_number: '',
                    password: '',
                    password_confirmation: ''
                },
                step: 1,
                userAccount: null,
                showPassword: false,
                requiresPassword: false,
                activeTab: 'mobileTab',
                isOpenVerifyMobileNumberModal: false,
                verification_code: null,
                loginFormRules: {
                    email: [
                        { required: true, message: 'Please enter your email', trigger: 'blur' },
                        { type: 'email', message: 'Please enter a valid email', trigger: 'change' }
                    ],
                    mobile_number: [
                        { required: true, message: 'Please enter your mobile number e.g 26771234567', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: 'Please enter your password.', trigger: 'blur' },
                        { type: 'string', min: 6, message: 'The password length must be more than 6 characters', trigger: 'change' }
                    ],
                    password_confirmation: [
                        { required: true, message: 'Confirm your password.', trigger: 'blur' },
                        { validator: samePasswordValidator, trigger: 'change' }
                    ]
                },
                mobileTabLabel: (h) => {
                    return h('div', [
                        h('Icon', {
                            props: {
                                type: 'ios-phone-portrait',
                                size: 20
                            }
                        }),
                        h('span', 'Mobile')
                    ])
                },
                emailTabLabel: (h) => {
                    return h('div', [
                        h('Icon', {
                            props: {
                                type: 'ios-mail-outline',
                                size: 20
                            }
                        }),
                        h('span', 'Email')
                    ])
                }
            }
        },
        computed: {
            serverEmailError(){
                return (this.serverErrors || {}).email;
            },
            serverMobileNumberError(){
                return (this.serverErrors || {}).mobile_number;
            },
            serverPasswordError(){
                return (this.serverErrors || {}).password;
            }
        },
        methods: {
            nextStep(){

                //  Reset the server errors
                this.resetErrors();

                this.userAccount = null;

                this.requiresPassword = false;

                this.loginForm.password = null;

                this.loginForm.password_confirmation = null;

                //  Validate the login form
                this.$refs['loginForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  Check if the account exists for the given email or mobile number
                        this.checkIfAccountExists()
                            .then(({data}) => {

                                this.isLoading = false;

                                var accountExists = false;

                                if(this.activeTab == 'emailTab'){

                                    accountExists = (data || {}).account_exists;

                                }else if(this.activeTab == 'mobileTab'){

                                    accountExists = (data || {}).account_exists;

                                }

                                //  If we have a matching account
                                if( accountExists ){

                                    //  If the user account requires a password
                                    if( ((data || {}).user || {}).requires_password ){

                                        this.requiresPassword = true;

                                    }

                                    //  Set the user account details
                                    this.userAccount = (data || {}).user;

                                    //  Proceed to step 2
                                    this.step = 2;

                                }else{

                                    this.$Message.warning({
                                        content: 'Account does not exist',
                                        duration: 6
                                    });

                                }

                            }).catch((response) => {

                                this.loginFail(response);

                            });

                    //  If the validation failed
                    } else {

                        if(this.activeTab == 'emailTab'){

                            this.$Message.warning({
                                content: 'Sorry, your email is not valid',
                                duration: 6
                            });

                        }else if(this.activeTab == 'mobileTab'){

                            this.$Message.warning({
                                content: 'Sorry, your mobile number is not valid',
                                duration: 6
                            });

                        }
                    }
                })
            },
            previousStep(){
                this.step = 1;
            },
            checkIfAccountExists(){

                this.isLoading = true;

                if(this.activeTab == 'emailTab'){

                    /** Attempt to fetch the user email account status using the auth fetchEmailAccountStatus
                     *  method found in the auth.js file
                     */
                    return auth.checkIfEmailAccountExists(this.loginForm.email);

                }else if(this.activeTab == 'mobileTab'){

                    /** Attempt to fetch the user mobile account status using the auth fetchMobileAccountStatus
                     *  method found in the auth.js file
                     */
                    return auth.checkIfMobileAccountExists(this.loginForm.mobile_number);

                }

            },
            handleOpenVerifyMobileNumberModal(){
                this.isOpenVerifyMobileNumberModal = true;
            },
            handleVerifiedMobileNumber(verification_code){

                this.verification_code = verification_code;
                this.requiresPassword = false;
                this.handleSubmit();

            },
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the login form
                this.$refs['loginForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  If the user account requires a password
                        if( this.requiresPassword ){

                            //  Allow the user to verify their mobile number
                            this.handleOpenVerifyMobileNumberModal();

                        }else{

                            //  Attempt to login
                            this.attemptLogin();

                        }

                    //  If the validation failed
                    } else {

                        this.$Message.warning({
                            content: 'Sorry, you cannot login yet',
                            duration: 6
                        });
                    }
                })
            },
            attemptLogin(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                if(this.activeTab == 'emailTab'){

                    //  Attempt to login using the auth loginWithEmail method found in the auth.js file
                    auth.loginWithEmail(this.loginForm.email, this.loginForm.password, this)
                        .then((data) => {

                            this.loginSuccess(data);

                        }).catch((response) => {

                            //  Stop loader
                            this.isLoading = false;

                    });

                }else if(this.activeTab == 'mobileTab'){

                    //  Attempt to login using the auth loginWithMobile method found in the auth.js file
                    auth.loginWithMobile(this.loginForm.mobile_number, this.loginForm.password,
                                         this.loginForm.password_confirmation, this.verification_code, this)
                        .then((data) => {

                            this.loginSuccess();

                        }).catch((response) => {

                            //  Stop loader
                            this.isLoading = false;

                    });

                }
            },
            loginSuccess(){

                //  Stop loader
                this.isLoading = false;

                //  resetForm() declared in miscMixin
                this.resetForm('loginForm');

                //  Login success message
                this.$Message.success({
                    content: 'You are signed in!',
                    duration: 6
                });

                //  Redirect the user to the projects page
                this.$router.push({ name: 'show-stores' });

            },
        }
    }
</script>
