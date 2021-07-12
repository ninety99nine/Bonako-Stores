<template>

    <span>{{ product.id }}</span>

</template>

<script>

    export default {
        props: {
            product: {
                type: Object,
                default: null
            },
            placement: {
                type: String,
                default: 'top'
            },
            canCancel: {
                type: Boolean,
                default: true
            },
        },
        data(){
            return {

            }
        }
    };

</script>
