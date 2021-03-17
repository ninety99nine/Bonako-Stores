<template>

    <!-- Pricing Poptip -->
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
            instantCart: {
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
                return this.instantCart.name;
            },
            description(){
                return this.instantCart.description;
            }
        }
    };

</script>
