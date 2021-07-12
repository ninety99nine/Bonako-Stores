<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
             :title="'Status: '+popularStore._attributes.visible.name"
             :content="popularStore._attributes.visible.description">

        <Badge :text="popularStore._attributes.visible.name" :type="color"></Badge>

    </Poptip>

</template>

<script type="text/javascript">
    export default {
        props: {
            popularStore: {
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

                if(this.popularStore._attributes.visible.status){

                    return 'success';

                }else{

                    return 'warning';

                }
            }
        }
    }
</script>
