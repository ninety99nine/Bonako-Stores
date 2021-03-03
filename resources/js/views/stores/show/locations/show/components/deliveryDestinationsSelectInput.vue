<template>

    <FormItem prop="type" :error="serverDeliveryDestinationsError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Title -->
            <span :class="['max-content-width', 'mr-2']">Delivery Destinations: </span>

            <!-- Select -->
            <Poptip trigger="hover" content="Which destinations do you deliver orders" word-wrap class="poptip-w-100">
                <Select id="select-delivery-destination" v-model="deliveryDestinationNames" filterable multiple 
                        :class="['w-100', 'mr-2']" :disabled="isLoading" allow-create :key="renderKey"
                        @on-create="handleAddDeliveryDestination($event)">
                    <Option v-for="(destination, index) in deliveryDestinations" :label="destination.name"
                            :value="destination.name" :key="index">
                        <span>{{ destination.name }}</span>
                        <span :style="{ color:'#ccc' }" class="float-right mr-4">
                            <span v-if="freeDelivery">Free delivery</span>
                            <span v-else-if="hasDeliveryFlatFee">Cost: {{ formatPrice(deliveryFlatFee, currencySymbol) }}</span>
                            <span v-else-if="destination.allow_free_delivery">Free delivery</span>
                            <span v-else-if="destination.cost > 0">Cost: {{ formatPrice(destination.cost, currencySymbol) }}</span>
                            <span v-else>Cost: None</span>
                        </span>
                    </Option>
                </Select>
            </Poptip>

        </div>

        <div v-if="locationForm.delivery_destinations.length" :class="['clearfix', 'my-2']">

            <!-- Manage Pricing Button -->
            <Button type="primary" size="small" class="float-right"
                    @click.native="isOpenManageDestinationsModal = true">
                <Icon type="ios-pin-outline" :size="20" class="mr-1"/>
                <span>Manage Destinations</span>
            </Button>
        
        </div>

        <!--
            MODAL EDIT LOCATION DESTINATIONS
        -->
        <template v-if="isOpenManageDestinationsModal">

            <manageDestinationsModal
                :location="locationForm"
                :freeDelivery="freeDelivery"
                :currencySymbol="currencySymbol"
                :hasDeliveryFlatFee="hasDeliveryFlatFee"
                @updated="handleUpdatedDestinations($event)"
                @visibility="isOpenManageDestinationsModal = $event">
            </manageDestinationsModal>

        </template>
        
    </FormItem>

</template>

<script>

    import manageDestinationsModal from './modal/manageDestinationsModal.vue';

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
            currencySymbol: {
                type: String,
                default: null
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            formatPrice: {
                type: Function
            }
        },
        components: { manageDestinationsModal },
        data(){
            return {
                renderKey: 1,
                deliveryDestinations: [],
                isOpenManageDestinationsModal: false
            }
        },
        computed: {
            serverDeliveryDestinationsError(){
                return (this.serverErrors || {}).delivery_destinations;
            },
            freeDelivery(){
                return this.locationForm.allow_free_delivery;
            },
            deliveryFlatFee(){
                return this.locationForm.delivery_flat_fee;
            },
            hasDeliveryFlatFee(){
                return (this.locationForm.delivery_flat_fee > 0);
            },
            deliveryDestinationNames: {
                get: function () {

                    //  Return the destination names only
                    return this.locationForm.delivery_destinations.map((destination, index) => {
                        return destination.name;
                    });

                },
                set: function (names) {

                    //  Get details of the selected destinations
                    var selectedDestinations = names.map((name, index) => {

                        //  Check if we have any destination already existing with this name
                        for (let i = 0; i < this.deliveryDestinations.length; i++) {

                            //  If we found a destination that matches the given name
                            if( this.deliveryDestinations[i].name == name ){

                                //  Return this already existing destination information
                                return this.deliveryDestinations[i];

                            }
                        }

                        //  Construct details of this new destination
                        var newDestination =  {
                            name: name,
                            cost: 0
                        };

                        //  Add the new destination to the delivery destinations
                        this.deliveryDestinations.push(newDestination);

                        //  Return this new destination
                        return newDestination;

                    });

                    //  Update the location delivery details with the selected destinations
                    this.$set(this.locationForm, 'delivery_destinations', selectedDestinations);
                }
            }
        },
        methods: {
            handleUpdatedDestinations(updatedDestinations){

                //  Update the location destinations
                this.deliveryDestinations = updatedDestinations;

                this.$set(this.locationForm, 'delivery_destinations', updatedDestinations);

            },
            handleAddDeliveryDestination(destination){

                //  Add the delivery destination to the local "deliveryDestinations" Array
                this.addDeliveryDestination(destination, 0, false, this.deliveryDestinations);

                //  Add the delivery destination to the location "delivery_destinations" Array
                this.addDeliveryDestination(destination, 0, false, this.locationForm.delivery_destinations);
                
                //  Reset the select input
                ++this.renderKey;

                //  DOM is not updated yet
                this.$nextTick(function () {

                    //  DOM is updated
                    
                    $('#select-delivery-destination').find('input[type="text"]').focus();

                });

            },
            addDeliveryDestination(name, cost, allow_free_delivery, target){

                //  If the delivery destination is set to null
                if( target == null ){

                    //  Convert it into an Array
                    target = [];

                }

                //  Check if the destination already exists
                var alreadyExists = target.filter((currDestination, index) => {
                    return currDestination.name == name;
                }).length ? true : false;

                //  If the destination already exists
                if( !alreadyExists ){

                    //  Add the destination
                    target.push({
                        name: name,
                        cost: cost,
                        allow_free_delivery: allow_free_delivery,
                    });

                }
            }
        },
        created(){

            //  Capture the location delivery destinations
            ((this.locationForm || {}).delivery_destinations || []).map((destination, index) => {

                this.addDeliveryDestination(destination.name, destination.cost.amount, destination.allow_free_delivery.status, this.deliveryDestinations);

            });

        }
    };

</script>
