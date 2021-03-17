<template>

    <!-- Order Customer Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <ListItem>Name: {{ customerName }}</ListItem>

        </List>

        <span>{{ customerFirstName }}</span>

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
                return this.customer._attributes.name || '...';
            },
            customerFirstName(){
                return this.customer.first_name || '...';
            },
        }
    };

</script>
