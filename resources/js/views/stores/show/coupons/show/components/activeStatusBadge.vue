<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
             :title="'Status: '+coupon.active.name" :content="coupon.active.description">

        <Badge :text="coupon.active.name" :type="type"></Badge>

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

                if(this.coupon.active.status){

                    return 'success';

                }else{

                    return 'warning';

                }
            }
        }
    }
</script>
