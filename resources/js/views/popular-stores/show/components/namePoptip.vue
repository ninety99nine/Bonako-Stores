<template>

    <span>{{ storeName }}</span>

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
        },
        computed: {
            storeName(){
                return ((this.popularStore._embedded || {}).store || {}).name;
            }
        }
    };

</script>
