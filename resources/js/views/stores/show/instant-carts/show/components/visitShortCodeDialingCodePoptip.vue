<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }">

        <template slot="content">

            <div v-if="visitShortCodeDialingCode !== 'None'">
                <span>Dial</span>
                <span :class="['font-weight-bold', 'text-primary', 'border-bottom-dashed', 'mx-1']" :style="{ fontSize: '1.5em' }">{{ visitShortCodeDialingCode }}</span>
                <span>to use instant cart</span>
            </div>

            <span v-else>No shortcode to dial</span>

        </template>

        <span :class="['text-primary']">{{ visitShortCodeDialingCode }}</span>

    </Poptip>

</template>

<script type="text/javascript">

    import countdown from './../../../../../../components/_common/countdown/default.vue';

    export default {
        props: {
            instantCart: {
                type: Object,
                default: null
            },
            placement: {
                type: String,
                default: 'top'
            }
        },
        components: { countdown },
        data(){
            return {

            }
        },
        computed: {
            visitShortCode(){
                return (this.instantCart['_attributes']['visit_short_code'] || {});
            },
            visitShortCodeDialingCode(){
                return this.visitShortCode.dialing_code || 'None';
            }
        }
    }
</script>
