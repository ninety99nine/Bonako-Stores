<template>

    <!-- Name Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <ListItem v-if="description">{{ description }}</ListItem>

        </List>

        <span>{{ name }}</span>

    </Poptip>

</template>

<script>

    export default {
        props: {
            coupon: {
                type: Object,
                default: null
            },
            placement: {
                type: String,
                default: 'top'
            },
            canCancel: {
                type: Boolean,
                default: true
            },
        },
        data(){
            return {

            }
        },
        computed: {
            name(){
                return this.coupon.name;
            },
            description(){
                return this.coupon.description;
            }
        }
    };

</script>
