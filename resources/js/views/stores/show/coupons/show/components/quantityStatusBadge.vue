<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
        :title="'Status: '+coupon.has_quantity_remaining.name" :content="coupon.has_quantity_remaining.description">

        <Badge :text="coupon.has_quantity_remaining.name" :type="type"></Badge>

    </Poptip>

</template>

<script type="text/javascript">
    export default {
        props: {
            coupon: {
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
            type(){

                if(this.coupon.has_quantity_remaining.status){

                    return 'success';

                }else{

                    return 'warning';

                }
            }
        }
    }
</script>
