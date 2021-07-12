<template>

    <span>{{ popularStore.id }}</span>

</template>

<script>

    export default {
        props: {
            popularStore: {
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
