<template>

    <Poptip word-wrap width="300" trigger="hover" placement="top" :style="{ wordBreak: 'break-word' }"
             :title="'Status: '+status.name" :content="status.description">
        <Tag :color="color">
            <span class="cut-text text-capitalize">{{ status.name }}</span>
        </Tag>
    </Poptip>

</template>

<script type="text/javascript">
    export default {
        props:{
            visibilityStatus: {
                type: Boolean,
                default: null
            }
        },
        data(){
            return {

            }
        },
        computed: {
            status(){

                if( typeof this.visibilityStatus === 'boolean' ){
                    return {
                        name: this.visibilityStatus ? 'Visible' : 'Hidden',
                        description: this.visibilityStatus
                            ? 'This product is visible from the store and can be selected by customers'
                            : 'This product is hidden from the store and cannot be selected by customers'
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
