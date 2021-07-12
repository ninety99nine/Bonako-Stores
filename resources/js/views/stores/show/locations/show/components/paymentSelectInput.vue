<template>

    <div>

        <FormItem prop="online_payment_methods" :error="serverOnlinePaymentMethodsError" class="mb-3">
            <div class="d-flex">
                <span :class="['max-content-width', 'mr-2']">Online Payments: </span>
                <Select v-model="locationForm.online_payment_methods" multiple
                        :disabled="isLoading || isLoadingPaymentMethods"
                        :class="['w-100', 'mr-2']">
                    <Option v-for="(paymentMethod, index) in onlinePaymentMethods"
                            :value="paymentMethod.id" :key="index">
                        {{ paymentMethod.name }}
                    </Option>
                </Select>
                <!-- Refresh Button -->
                <Poptip trigger="hover" content="Refresh the payment methods" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchPaymentMethods()">
                        <Icon type="ios-refresh" :size="20" />
                    </Button>
                </Poptip>
            </div>
        </FormItem>

        <FormItem prop="offline_payment_methods" :error="serverOfflinePaymentMethodsError" class="mb-3">
            <div class="d-flex">
                <span :class="['max-content-width', 'mr-2']">Offline Payments: </span>
                <Select v-model="locationForm.offline_payment_methods" multiple
                        :disabled="isLoading || isLoadingPaymentMethods"
                        :class="['w-100', 'mr-2']">
                    <Option v-for="(paymentMethod, index) in offlinePaymentMethods"
                            :value="paymentMethod.id" :key="index">
                        {{ paymentMethod.name }}
                    </Option>
                </Select>
                <!-- Refresh Button -->
                <Poptip trigger="hover" content="Refresh the payment methods" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchPaymentMethods()">
                        <Icon type="ios-refresh" :size="20" />
                    </Button>
                </Poptip>
            </div>
        </FormItem>

    </div>


</template>

<script>

    export default {
        props: {
            locationForm: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        data(){
            return {
                paymentMethods: [],
                isLoadingPaymentMethods: false
            }
        },
        computed: {
            serverOnlinePaymentMethodsError(){
                return (this.serverErrors || {}).online_payment_methods;
            },
            serverOfflinePaymentMethodsError(){
                return (this.serverErrors || {}).offline_payment_methods;
            },
            onlinePaymentMethods(){
                return this.paymentMethods.filter((paymentMethod, index) => {
                    return paymentMethod.used_online;
                });
            },
            offlinePaymentMethods(){
                return this.paymentMethods.filter((paymentMethod, index) => {
                    return paymentMethod.used_offline;
                });
            },
            paymentMethodsUrl(){
                /**  Note "api_home" is defined within the auth.js file.
                 *   It holds reference to common links for ease of
                 *   access.
                 */
                return api_home['_links']['bos:payment_methods'].href;
            },
        },
        methods: {

            fetchPaymentMethods() {

                //  Hold constant reference to the current Vue instance
                const self = this;

                //  Start loader
                self.isLoadingPaymentMethods = true;

                //  Use the api call() function, refer to api.js
                api.call('get', this.paymentMethodsUrl)
                    .then(({data}) => {

                        //  Stop loader
                        self.isLoadingPaymentMethods = false;

                        //  Get the payment methods
                        self.paymentMethods = ((data || [])['_embedded'] || [])['payment_methods'];

                    })
                    .catch(response => {

                        //  Stop loader
                        this.isLoadingPaymentMethods = false;

                    });

            }

        },
        created(){
            return this.fetchPaymentMethods();
        }
    };

</script>
