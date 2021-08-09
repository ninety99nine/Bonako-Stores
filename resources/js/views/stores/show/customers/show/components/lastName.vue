<template>

    <span>{{ lastName }}</span>

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
            lastName(){
                return this.customer['_embedded']['user'].last_name;
            }
        }
    };

</script>
