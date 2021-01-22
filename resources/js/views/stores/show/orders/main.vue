<template>

    <Row class="mt-3 pt-2">

        <Col :span="22" :offset="1">

            <Row :gutter="12" :class="['border-bottom-dashed', 'mb-4', 'mt-3', 'pb-4']">

                <Col :span="12">

                    <!-- Heading -->
                    <h1 :class="['font-weight-bold', 'text-muted']">
                        Orders
                    </h1>

                </Col>

                <Col :span="12">

                    <div class="clearfix">

                        <!-- Watch Video Button -->
                        <Button type="primary" size="default" @click.native="fetchStores()">
                            <Icon type="ios-play-outline" class="mr-1" :size="20" />
                            <span>Watch Video</span>
                        </Button>

                    </div>

                </Col>

            </Row>

            <!-- Orders -->
            <ordersTable :location="location"></ordersTable>

        </Col>

    </Row>

</template>

<script>

    import ordersTable from './list/main.vue';

    export default {
        props: {
            store: {
                type: Object,
                default: null
            },
            location: {
                type: Object,
                default: null
            },
        },
        components: { ordersTable },
        data () {
            return {

            }
        }
    }
</script>
