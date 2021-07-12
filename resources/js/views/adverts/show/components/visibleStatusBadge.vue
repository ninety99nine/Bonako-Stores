<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
             :title="'Status: '+advert._attributes.visible.name"
             :content="advert._attributes.visible.description">

        <Badge :text="advert._attributes.visible.name" :type="color"></Badge>

    </Poptip>

</template>

<script type="text/javascript">
    export default {
        props: {
            advert: {
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

                if(this.advert._attributes.visible.status){

                    return 'success';

                }else{

                    return 'warning';

                }
            }
        }
    }
</script>
