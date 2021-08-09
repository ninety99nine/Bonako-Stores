<template>

    <span>{{ customer.id }}</span>

</template>

<script>

    export default {
        props: {
            customer: {
                type: Object,
                default: null
            }
        }
    };

</script>
