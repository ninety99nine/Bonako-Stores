<template>

    <!-- Order Customer Contacts Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <ListItem>Mobile: {{ customerMobileNumber }}</ListItem>

        </List>

        <span>{{ customerMobileNumber }}</span>

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
            customerMobileNumber(){
                return this.customer.mobile_number || '...';
            },
        }
    };

</script>
