<template>

    <!-- Order # Poptip -->
    <Poptip trigger="hover" :placement="placement" width="300">

        <List slot="content" size="small">

            <ListItem v-if="number">{{ number }}</ListItem>

            <!-- Note "isCancelled" from mixins -->
            <ListItem v-if="isCancelled">This order is cancelled</ListItem>

        </List>

        <!-- Note "cancelledClass" from mixins -->
        <span :class="cancelledClass">{{ number }}</span>

    </Poptip>

</template>

<script>

    import localMixin from './../_mixins/main.vue';

    export default {
        mixins: [ localMixin ],
        props: {
            order: {
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
            number(){
                return this.order.number;
            }
        }
    };

</script>
