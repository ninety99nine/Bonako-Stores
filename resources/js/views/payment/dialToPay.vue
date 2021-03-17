<template>

    <div>

        <!-- If we are loading, Show Loader -->
        <Loader v-show="isLoading" class="mt-2">Loading...</Loader>

        <template v-if="hasPaymentShortCode">

            <div :class="dialToPayClass">

                <span>Dial to pay: </span>
                <span :class="['text-primary', 'font-weight-bold']">{{ paymentShortCode.dialing_code }}</span>

                <!-- Show the short code details -->
                <Poptip trigger="hover" :placement="placement" word-wrap width="300">

                    <div slot="content" class="py-2" :style="{ lineHeight: 'normal' }">
                        <p>Dial <span class="text-primary">{{ paymentShortCode.dialing_code }}</span> to pay <template v-if="name">for your {{ name }}</template></p>
                    </div>

                    <!-- Show the info icon -->
                    <Icon type="ios-information-circle-outline" :size="16" />

                </Poptip>

            </div>

            <!-- Payment Short Code Countdown timer -->
            <transition name="slide-right-fade">

                <countdown v-if="paymentShortCodeExpiryTime" :datetime="paymentShortCodeExpiryTime"
                            position="right" :class="countdownClass" @expired="handlePaymentShortcodeExpiryStatus()">
                </countdown>

            </transition>

        </template>

        <template v-else>

            <!-- Pay Now Button -->
            <span :class="['bg-light', 'rounded-pill', 'd-inline-block', 'px-3', 'py-1', 'mt-1', 'mr-2']">{{ description }}</span>

            <!-- Pay Now Button -->
            <Button v-if="!isLoading" type="success" class="float-right" @click.native.stop="generatePaymentShortcode()">Pay now</Button>

        </template>

    </div>

</template>

<script>

    import Loader from './../../components/_common/loaders/default.vue';
    import countdown from './../../components/_common/countdown/default.vue';

    export default {
        components: { Loader, countdown },
        props: {
            resource: {
                type: Object,
                default: null
            },
            name:{
                type: String,
                default: 'Example'
            },
            description:{
                type: String,
                default: 'Pay to access this example'
            },
            placement:{
                type: String,
                default: 'top'
            },
            dialToPayClass:{
                type: Array,
                default: function(){
                    return ['bg-light', 'rounded-pill', 'px-3', 'py-1']
                }
            },
            countdownClass:{
                type: Array,
                default: function(){
                    return ['mt-2', 'mr-2']
                }
            }
        },
        data(){
            return {
                isLoading: false
            }
        },
        watch: {
            //  Watch changes on isLoading
            isLoading (newVal, oldVal) {

                //  Notify parent on changes
                this.$emit('isLoading', newVal);

            }
        },
        computed: {
            paymentShortCode(){
                return this.resource['_attributes']['payment_short_code'];
            },
            hasPaymentShortCode(){
                return this.paymentShortCode ? true : false;
            },
            paymentShortCodeExpiryTime(){
                return (this.paymentShortCode || {}).expires_at;
            },
            generatePaymentShortcodeUrl(){
                return this.resource['_links']['bos:generate-payment-shortcode'].href;
            },
        },
        methods: {
            handlePaymentShortcodeExpiryStatus(){

                //  Notify parent
                this.$emit('updated');

            },
            generatePaymentShortcode(){

                if( this.generatePaymentShortcodeUrl ){

                    //  Hold constant reference to the current Vue instance
                    const self = this;

                    //  Start loader
                    self.isLoading = true;

                    return api.call('post', this.generatePaymentShortcodeUrl)
                        .then(({data}) => {

                            //  Stop loader
                            self.isLoading = false;

                            //  Notify parent
                            this.$emit('updated');

                        }).catch((response) => {

                            //  Stop loader
                            self.isLoading = false;

                    });

                }
            }
        }
    }
</script>
