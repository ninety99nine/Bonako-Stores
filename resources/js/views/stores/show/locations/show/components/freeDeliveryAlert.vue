<template>

    <Alert v-if="freeDelivery" type="success" class="mt-2">

        <span>This location offers <span :class="['font-weight-bold', 'text-success']">FREE</span> delivery to all destinations</span>

        <Poptip trigger="hover" placement="top" word-wrap width="300"
                content="This location offers free delivery for orders placed. This means that customers placing orders will not be charged separately for delivery">

            <!-- Show the info icon -->
            <Icon type="ios-information-circle-outline" :size="16" />

        </Poptip>
    
    </Alert>

</template>

<script>

    export default {
        props: {
            location: {
                type: Object,
                default: null
            }
        },
        computed: {
            freeDelivery(){
                return this.location.allow_free_delivery;
            }
        }
    };

</script>
