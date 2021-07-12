<template>

    <span>{{ instantCart.id }}</span>

</template>

<script>

    export default {
        props: {
            instantCart: {
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
