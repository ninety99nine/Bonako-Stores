<template>

    <!-- Name Poptip -->
    <Poptip word-wrap width="300" trigger="hover" :placement="placement"
            :style="{ wordBreak: 'break-word' }" title="Description"
            :content="description">

        <span>{{ name }}</span>

    </Poptip>

</template>

<script>

    export default {
        props: {
            coupon: {
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
            name(){
                return this.coupon.name;
            },
            description(){
                return this.coupon.description || 'No description';
            }
        }
    };

</script>
