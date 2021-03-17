<template>

    <Alert v-if="alwaysApply" type="success" class="mt-2">

        <span>This coupon is applied <span :class="['font-weight-bold', 'text-success']">Automatically</span> for every cart.</span>

        <Poptip trigger="hover" placement="top" word-wrap width="300"
                content="This coupon is applied automatically for every order placed. The customer does not have to manually apply it.">

            <!-- Show the info icon -->
            <Icon type="ios-information-circle-outline" :size="16" />

        </Poptip>

    </Alert>

</template>

<script>

    export default {
        props: {
            couponForm: {
                type: Object,
                default: null
            }
        },
        computed: {
            alwaysApply(){
                return this.couponForm.always_apply;
            }
        }
    };

</script>
