<template>

    <Poptip word-wrap width="250" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
            :content="transactionAmount">
        <span>{{ transactionAmount }}</span>
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
            transaction(){
                return this.subscription._embedded.transaction;
            },
            transactionAmount(){
                if( this.transaction ){
                    return this.transaction.amount.currency_money;
                }
            }
        }
    };

</script>
