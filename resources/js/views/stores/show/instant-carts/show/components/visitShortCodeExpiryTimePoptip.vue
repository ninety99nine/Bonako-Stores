<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }">

        <div slot="content">

            <countdown :datetime="visitShortCodeExpiryTime" position="left"
                       @expired="handleVisitShortcodeExpiryStatus()">
            </countdown>

        </div>

        <span>{{ visitShortCodeExpiryTimeFormated }}</span>

    </Poptip>

</template>

<script type="text/javascript">

    import miscMixin from './../../../../../../components/_mixins/misc/main.vue';
    import countdown from './../../../../../../components/_common/countdown/default.vue';

    export default {
        mixins: [ miscMixin ],
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
            visitShortCodeExpiryTime(){
                return this.visitShortCode.expires_at;
            },
            visitShortCodeExpiryTimeFormated(){
                if( this.visitShortCodeExpiryTime ){
                    return this.formatDateTime(this.visitShortCodeExpiryTime, 'MMM DD YYYY H:mmA');
                }
            }
        },
        methods: {
            handleVisitShortcodeExpiryStatus(){

            }
        }
    }
</script>
