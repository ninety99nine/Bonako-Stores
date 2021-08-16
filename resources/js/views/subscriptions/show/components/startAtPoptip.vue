<template>

    <Poptip word-wrap width="220" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }">

        <span slot="content" :class="['text-center', 'd-block']">
            <span>Start subscription on </span>
            <span :class="['text-success', 'd-block']" :style="{ fontSize: '20px' }">{{ start_at }}</span>
        </span>

        <span>{{ start_at }}</span>

    </Poptip>

</template>

<script>

    import miscMixin from './../../../../components/_mixins/misc/main.vue';

    export default {
        mixins: [miscMixin],
        props: {
            subscription: {
                type: Object,
                default: null
            },
            placement: {
                type: String,
                default: 'top'
            }
        },
        data(){
            return {

            }
        },
        computed: {
            start_at(){
                return this.formatDateTime(this.subscription.start_at, 'DD MMM YYYY HH:mm');
            }
        }
    };

</script>
