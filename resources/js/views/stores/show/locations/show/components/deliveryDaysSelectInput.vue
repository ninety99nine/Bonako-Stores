<template>

    <FormItem prop="type" :error="serverDeliveryDaysError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Title -->
            <span :class="['max-content-width', 'mr-2']">Delivery Days: </span>

            <!-- Select -->
            <Poptip trigger="hover" content="Which days do you deliver orders" word-wrap class="poptip-w-100">
                <Select v-model="locationForm.delivery_days" filterable multiple :class="['w-100', 'mr-2']"
                        :disabled="isLoading">
                    <Option v-for="(day, index) in deliveryDays" :value="day" :key="index">{{ day }}</Option>
                </Select>
            </Poptip>

        </div>

    </FormItem>

</template>

<script>

    export default {
        props: {
            locationForm: {
                type: Object,
                default: null
            },
            isLoading: {
                type: Boolean,
                default: false
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
        },
        data(){
            return {
                deliveryDays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            }
        },
        computed: {
            serverDeliveryDaysError(){
                return (this.serverErrors || {}).delivery_days;
            }
        },
    };

</script>
