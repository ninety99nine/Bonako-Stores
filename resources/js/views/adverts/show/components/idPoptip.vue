<template>

    <span>{{ advert.id }}</span>

</template>

<script>

    export default {
        props: {
            advert: {
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
