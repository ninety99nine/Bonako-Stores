<template>

    <Poptip word-wrap width="400" trigger="hover" placement="top"
        :style="{ wordBreak: 'break-word', textAlign: 'start' }"
        title="Checkout Summary">

        <div slot="content">

            <div v-for="(record, index) in customerStats" :key="index" :class="[(record.name == 'Grand total' ? 'bg-grey-light pt-2' : '')]">
                <span>{{ record.name }}</span>
                <span :class="[(record.name == 'Grand total' ? '' : ''), 'font-weight-bold', 'ml-1']">{{ record.value }}</span>

                <div v-if="['Grand total', 'Instant carts used'].includes(record.name)" class="border-bottom-dashed my-2"></div>
            </div>

            <div class="border-top-dashed pt-1">This is a summary of the orders placed by the customer after checking out of the store. This summary includes both paid, unpaid, delivered and undelivered orders.</div>

        </div>

        <Icon type="ios-information-circle-outline" :size="20" />

    </Poptip>

</template>

<script>

    import localMixin from './../mixin/main.vue'

    export default {
        mixins: [localMixin],
        props: {
            localCustomer: {
                type: Object,
                default: null
            }
        },
        data(){
            return {

            }
        }
    };

</script>
