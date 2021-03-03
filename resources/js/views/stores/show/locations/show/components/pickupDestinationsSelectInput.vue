<template>

    <FormItem prop="type" :error="serverPickupDestinationsError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Title -->
            <span :class="['max-content-width', 'mr-2']">Pickup Destinations: </span>

            <!-- Select -->
            <Poptip trigger="hover" content="Which destinations do you allow customers to pickup their orders" word-wrap class="poptip-w-100">
                <Select id="select-pickup-destination" v-model="locationForm.pickup_destinations" filterable multiple 
                        :class="['w-100', 'mr-2']" :disabled="isLoading" allow-create :key="renderKey"
                        @on-create="handleAddPickupDestination($event)">
                    <Option v-for="(destination, index) in pickupDestinations" :value="destination" :key="index">{{ destination }}</Option>
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
                renderKey: 1,
                pickupDestinations: []
            }
        },
        computed: {
            serverPickupDestinationsError(){
                return (this.serverErrors || {}).pickup_destinations;
            }
        },
        methods: {
            handleAddPickupDestination(destination){

                //  Add the pickup destination to the local "pickupDestinations" Array
                this.addPickupDestination(destination, this.pickupDestinations);

                //  Add the pickup destination to the location "pickup_destinations" Array
                this.addPickupDestination(destination, this.locationForm.pickup_destinations);
                
                //  Reset the select input
                ++this.renderKey;

                //  DOM is not updated yet
                this.$nextTick(function () {
                    
                    $('#select-pickup-destination').find('input[type="text"]').focus();

                });

            },
            addPickupDestination(destination, target){

                //  If the pickup destination is set to null
                if( target == null ){

                    //  Convert it into an Array
                    target = [];

                }

                //  Check if the destination already exists
                var alreadyExists = target.filter((currDestination, index) => {
                    return currDestination == destination;
                }).length ? true : false;

                //  If the destination already exists
                if( !alreadyExists ){

                    //  Add the destination
                    target.push(destination);

                }
            }

        },
        created(){

            //  Capture the location pickup destinations
            ((this.locationForm || {}).pickup_destinations || []).map((destination, index) => {
                this.addPickupDestination(destination, this.pickupDestinations);
            });

        }
    };

</script>
