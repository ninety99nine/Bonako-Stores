<template>

    <Poptip word-wrap width="250" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
            :content="subscriptionPlanDescription">
        <span>{{ subscriptionPlanName }}</span>
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
            subscriptionPlan(){
                return this.subscription._embedded.subscription_plan;
            },
            subscriptionPlanName(){
                if( this.subscriptionPlan ){
                    return this.subscriptionPlan.name;
                }
            },
            subscriptionPlanDescription(){
                if( this.subscriptionPlan ){
                    return this.subscriptionPlan.description;
                }
            }
        }
    };

</script>
