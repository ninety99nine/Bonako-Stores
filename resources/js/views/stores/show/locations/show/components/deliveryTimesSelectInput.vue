<template>

    <FormItem prop="type" :error="serverDeliveryTimesError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Title -->
            <span :class="['max-content-width', 'mr-2']">Delivery Times: </span>

            <!-- Select -->
            <Poptip trigger="hover" content="Which times do you deliver orders" word-wrap class="poptip-w-100">
                <Select v-model="locationForm.delivery_times" filterable multiple :class="['w-100']"
                        :disabled="isLoading" allow-create @on-create="addDeliveryTime($event, locationForm.delivery_times)">
                    <Option v-for="(time, index) in deliveryTimes" :value="time" :key="index">{{ time }}</Option>
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
                deliveryTimes: [
                    '6am', '7am', '8am', '9am', '10am', '11am', '12pm',
                    '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm', '11pm'
                ]
            }
        },
        computed: {
            serverDeliveryTimesError(){
                return (this.serverErrors || {}).delivery_times;
            }
        },
        methods:{
            
            addDeliveryTime(time, target){

                //  If the delivery times are set to null
                if( target == null ){

                    //  Convert it into an Array
                    target = [];

                }

                //  Check if the time already exists
                var alreadyExists = target.filter((currTime, index) => {
                    return currTime == time;
                }).length ? true : false;

                //  If the time already exists
                if( !alreadyExists ){

                    //  Add the time
                    target.push(time);

                }
            }

        }
    };

</script>
