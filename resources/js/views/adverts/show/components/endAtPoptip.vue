<template>

    <Poptip word-wrap width="200" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }">

        <span slot="content" :class="['text-center', 'd-block']">
            <span>End listing on </span>
            <span :class="['text-success', 'd-block']" :style="{ fontSize: '20px' }">{{ end_at }}</span>
        </span>

        <span>{{ end_at }}</span>

    </Poptip>

</template>

<script>

    import miscMixin from './../../../../components/_mixins/misc/main.vue';

    export default {
        mixins: [miscMixin],
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
        },
        computed: {
            end_at(){
                return this.formatDateTime(this.advert.end_at, 'DD MMM YYYY HH:mm');
            }
        }
    };

</script>
