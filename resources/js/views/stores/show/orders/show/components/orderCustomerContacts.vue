<template>

    <!-- Order Customer Contacts Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <span slot="content" :class="['text-center', 'd-block']">
            <span>Contact </span>
            <span :class="['text-success', 'd-block']" :style="{ fontSize: '20px' }">{{ mobileCallingNumber }}</span>
        </span>

        <span>{{ mobileNumber }}</span>

    </Poptip>

</template>

<script>

    import localMixin from './../_mixins/main.vue';

    export default {
        mixins: [ localMixin ],
        props: {
            order: {
                type: Object,
                default: null
            },
            placement: {
                type: String,
                default: 'top'
            }
        },
        data(){
            return {

            }
        },
        computed: {
            customer(){
                return (this.order._embedded.customer || {});
            },
            customerName(){
                return ((((this.customer._embedded || {}).user || {})._attributes || {}).name || '...');
            },
            mobileNumber(){
                return (((this.customer._embedded || {}).user || {}).mobile_number || [])['number'];
            },
            mobileCallingNumber(){
                return (((this.customer._embedded || {}).user || {}).mobile_number || [])['calling_number'];
            }
        }
    };

</script>
