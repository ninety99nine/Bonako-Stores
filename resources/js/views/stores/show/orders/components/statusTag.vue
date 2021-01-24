<template>

    <Poptip word-wrap width="350" trigger="hover" placement="top"
             :title="'Status: '+status.name" :content="status.description">
        <Tag :color="color">
            <span class="cut-text text-capitalize">{{ status.name }}</span>
        </Tag>
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

                if(['Open', 'Fulfilled', 'Paid'].includes(this.status.name)){

                    return 'success';

                }else if(['Unfulfilled', 'Unpaid'].includes(this.status.name)){

                    return 'warning';

                }else if(['Cancelled'].includes(this.status.name)){

                    return 'error';

                }else if(['Closed'].includes(this.status.name)){

                    return 'default';

                }
            }
        }
    }
</script>
