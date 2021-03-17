<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
             title="Instruction" :content="'Dial '+visitShortCodeDialingCode+' to use instant cart'">

        <List slot="content" size="small">

            <ListItem>

                <span :class="['font-weight-bold', 'text-primary', 'border-bottom-dashed']" :style="{ fontSize: '1.5em' }">{{ visitShortCodeDialingCode }}</span>

            </ListItem>

            <ListItem v-if="visitShortCodeExpiryTime">

                <countdown :datetime="visitShortCodeExpiryTime"
                        position="right" @expired="handleVisitShortcodeExpiryStatus()">
                </countdown>

            </ListItem>

        </List>

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
                return this.visitShortCode.dialing_code;
            },
            visitShortCodeExpiryTime(){
                return this.visitShortCode.expires_at;
            },
        },
        methods: {
            handleVisitShortcodeExpiryStatus(){

            }
        }
    }
</script>
