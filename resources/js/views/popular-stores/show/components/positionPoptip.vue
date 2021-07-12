<template>

    <Poptip word-wrap width="200" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }">

        <span slot="content" :class="['text-center', 'd-block']">
            <span>Listed in position </span>
            <span :class="['text-success']" :style="{ fontSize: '20px' }">#{{ arrangement }}</span>
        </span>

        <span>{{ arrangement }}</span>

    </Poptip>

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
            arrangement(){
                return this.popularStore.arrangement;
            }
        }
    };

</script>
