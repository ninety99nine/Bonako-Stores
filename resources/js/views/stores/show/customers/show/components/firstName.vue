<template>

    <span>{{ firstName }}</span>

</template>

<script>

    export default {
        props: {
            customer: {
                type: Object,
                default: null
            }
        },
        computed: {
            firstName(){
                return this.customer['_embedded']['user'].first_name;
            }
        }
    };

</script>
