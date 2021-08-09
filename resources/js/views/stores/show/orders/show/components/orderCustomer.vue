<template>

    <!-- Order Customer Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <span slot="content" :class="['text-center', 'd-block']">
            <span>Customer </span>
            <span :class="['text-success', 'd-block']" :style="{ fontSize: '20px' }">{{ customerName }}</span>
        </span>

        <a href="#" @click.prevent="viewCustomer()">{{ customerFirstName }}</a>

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
            customerFirstName(){
                return (((this.customer._embedded || {}).user || {}).first_name || '...');
            }
        },
        methods: {
            viewCustomer(){

                let params = Object.assign({}, this.$route.params, { customer_url: encodeURIComponent(this.customer._links.self.href) });

                this.$router.push({ name: 'show-store-customer', params: params }).catch(()=>{

                    //  Handle redundant navigation by refreshing the page
                    this.$router.go();

                });
            }
        }
    };

</script>
