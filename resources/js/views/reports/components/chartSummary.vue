<template>
    <span v-if="summary" class="bg-grey-light d-block py-2 px-4 w-100">
        <span class="font-weight-bold">Summary:</span>
        <span v-html="summary"></span>
    </span>
</template>

<script>


export default {
    props: {
        summary: {
            type: String,
            default: null
        }
    },
    data() {
        return {

        };
    }
};
</script>
