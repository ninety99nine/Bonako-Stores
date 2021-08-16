<template>

    <Poptip word-wrap width="200" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
            :content="activeDescription">
        <Tag :color="activeStatus ? 'green' : 'default'">{{ activeName }}</Tag>
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
            activeStatus(){
                return this.subscription.active.status;
            },
            activeName(){
                return this.subscription.active.name;
            },
            activeDescription(){
                return this.subscription.active.description;
            }
        }
    };

</script>
