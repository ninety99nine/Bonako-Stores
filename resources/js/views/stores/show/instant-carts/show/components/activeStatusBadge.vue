<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
             :title="'Status: '+instantCart.active.name" :content="instantCart.active.description">

        <Badge :text="instantCart.active.name" :type="color"></Badge>

    </Poptip>

</template>

<script type="text/javascript">
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
        data(){
            return {

            }
        },
        computed: {
            color(){

                if(this.instantCart.active.status){

                    return 'success';

                }else{

                    return 'warning';

                }
            }
        }
    }
</script>
