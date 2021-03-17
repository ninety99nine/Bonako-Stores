<template>

    <Poptip word-wrap width="350" trigger="hover" placement="top"
             :title="'Status: '+status.name" :content="status.description">

        <Badge :text="status.name" :type="color"></Badge>

    </Poptip>

</template>

<script type="text/javascript">
    export default {
        props:{
            status: {
                type: Object,
                default: null
            }
        },
        data(){
            return {

            }
        },
        computed: {
            color(){

                if(['Open', 'Delivered', 'Paid'].includes(this.status.name)){

                    return 'success';

                }else if(['Undelivered', 'Unpaid', 'Pending'].includes(this.status.name)){

                    return 'warning';

                }else if(['Cancelled'].includes(this.status.name)){

                    return 'default';

                }
            }
        }
    }
</script>
