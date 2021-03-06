<template>

    <Row>
        <Col span="8" :offset="8">
            <Card class="auth-form mt-5 pt-2">

                <!-- Heading -->
                <Divider orientation="left" class="font-weight-bold">Reset Your Password</Divider>

                <p class="text-dark mb-3 pb-3 border-bottom-dashed">Forgot your password? No worries. Just enter the email you used to sign up and we'll send you a link to reset it.</p>

                <!-- Error Message Alert -->
                <Alert v-if="serverErrorMessage && !isLoading" type="warning">{{ serverErrorMessage }}</Alert>

                <!-- Success Message Alert -->
                <Alert v-if="serverSuccessMessage && !isLoading" type="success">{{ serverSuccessMessage }}</Alert>

                <Form ref="passwordResetForm" :model="passwordResetForm" :rules="passwordResetFormRules">

                    <!-- Enter Email -->
                    <FormItem prop="email" :error="serverEmailError">
                        <Input type="email" v-model="passwordResetForm.email" placeholder="Email" :disabled="isLoading"
                               @keyup.enter.native="handleSubmit()">
                            <Icon type="ios-mail-outline" slot="prepend"></Icon>
                        </Input>
                    </FormItem>

                    <!-- Login Link -->
                    <router-link to="login" class="mr-2">Login?</router-link>

                    <!-- Register Link -->
                    <router-link to="register">Create Account?</router-link>

                    <!-- Send Button -->
                    <FormItem v-if="!isLoading">
                        <Button type="primary" icon="ios-paper-plane-outline" class="float-right" :disabled="isLoading" @click="handleSubmit()">
                            {{ emailSent ? 'Resend' : 'Send Password Reset Email' }}
                        </Button>
                    </FormItem>

                    <!-- If we are loading, Show Loader -->
                    <Loader v-show="isLoading" class="mt-2">Sending email...</Loader>

                </Form>
            </Card>
        </Col>
    </Row>

</template>
<script>

    import miscMixin from './../../../components/_mixins/misc/main.vue';
    import Loader from './../../../components/_common/loaders/default.vue';

    export default {
        mixins: [miscMixin],
        components: { Loader },
        data () {
            return {
                emailSent: false,
                isLoading: false,
                passwordResetForm: {
                    email: ''
                },
                passwordResetFormRules: {
                    email: [
                        { required: true, message: 'Please enter your email', trigger: 'blur' },
                        { type: 'email', message: 'Please enter a valid email', trigger: 'blur' }
                    ]
                }
            }
        },
        computed: {
            serverEmailError(){
                return (this.serverErrors || {}).email;
            }
        },
        methods: {
            handleSubmit(){

                //  Reset the server errors
                this.resetErrors();

                //  Validate the password reset form
                this.$refs['passwordResetForm'].validate((valid) =>
                {
                    //  If the validation passed
                    if (valid) {

                        //  Attempt to send password reset link
                        this.attemptSendPasswordResetLink();

                    //  If the validation failed
                    } else {
                        this.$Message.warning({
                            content: 'Sorry, we cannot send your password reset link yet',
                            duration: 6
                        });
                    }
                })
            },
            attemptSendPasswordResetLink(){

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoading = true;

                //  Attempt to send the password reset link using the auth sendPasswordResetLink method found in the auth.js file
                auth.sendPasswordResetLink(this.passwordResetForm.email)
                    .then((data) => {

                        //  Stop loader
                        self.isLoading = false;

                        //  Indicate that the email has already been sent
                        self.emailSent = true;

                        //  resetForm() declared in miscMixin
                        self.resetForm('passwordResetForm');

                        //  Password reset link sent success message
                        self.$Message.success({
                            content: 'Password reset link sent! Check your email',
                            duration: 6
                        });

                        message

                    }).catch((response) => {

                        //  Stop loader
                        self.isLoading = false;

                });
            }
        }
    }
</script>
