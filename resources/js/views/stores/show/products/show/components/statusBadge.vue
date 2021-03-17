<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
             :title="'Status: '+status.name" :content="status.description">

        <Badge :text="status.name" :type="color"></Badge>

    </Poptip>

</template>

<script type="text/javascript">
    export default {
        props:{
            visible: {
                type: Object,
                default: null
            }
        },
        data(){
            return {

            }
        },
        computed: {
            status(){

                if( typeof this.visible === 'object' ){
                    return {
                        name: this.visible.name,
                        description: this.visible.description
                    };
                }else{
                    return {
                        name: null,
                        description: null
                    };
                }
            },
            color(){

                if(['Visible'].includes(this.status.name)){

                    return 'success';

                }else if(['Hidden'].includes(this.status.name)){

                    return 'warning';

                }else {

                    return 'default';

                }
            }
        }
    }
</script>
