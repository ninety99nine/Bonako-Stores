<template>
    <div>
        <!-- Modal 

             Note: modalVisible and detectClose() are imported from the modalMixin.
             They are used to allow for opening and closing the modal properly
             during the v-if conditional statement of the parent component. It
             is important to note that <Modal> does not open/close well with
             v-if statements by default, therefore we need to add additional
             functionality to enhance the experience. Refer to modalMixin.
        -->
        <Modal
            width="800"
            v-model="modalVisible"
            title="Manage Destinations"
            @on-visible-change="detectClose">

            <!-- Form -->
            <Form ref="locationForm" :model="locationForm">

                <!-- Disclaimer: Free Delivery -->
                <freeDeliveryAlert :location="location"></freeDeliveryAlert>

                <!-- Disclaimer: No Price -->
                <flatFeeDeliveryAlert :location="location" :currencySymbol="currencySymbol"></flatFeeDeliveryAlert>

                <Row :gutter="10" class="m-2">

                    <Col :span="10">

                        <span class="font-weight-bold">Destination Name</span>

                    </Col>

                    <Col :span="14">

                        <span class="font-weight-bold">Delivery Cost ({{ currencySymbol }})</span>

                    </Col>

                </Row>

                <!-- Destination List & Dragger  -->
                <draggable
                    :list="locationForm.delivery_destinations"
                    @start="drag=true" 
                    @end="drag=false" 
                    :options="{
                        group:'delivery-destinations', 
                        handle:'.dragger-handle',
                        draggable:'.delivery-destination',
                    }">

                    <Row :gutter="12" v-for="(destination, index) in locationForm.delivery_destinations" 
                         :key="index" :class="['delivery-destination', 'rounded', 'p-2']">

                        <Col :span="10">

                            <Input type="text" v-model="destination.name" placeholder="Name" 
                                    @keyup.enter.native="handleSubmit()">
                            </Input>

                        </Col>

                        <Col :span="5">

                            <!-- Enter Cost -->
                            <InputNumber v-model.number="destination.cost" :class="['w-100', 'mr-2']" placeholder="40"
                                        :disabled="freeDelivery || hasDeliveryFlatFee || destination.allow_free_delivery"
                                        @keyup.enter.native="handleSubmit()">
                            </InputNumber>

                        </Col>

                        <Col :span="5">

                            <!-- Free delivery checkbox -->
                            <Checkbox v-model="destination.allow_free_delivery" :disabled="freeDelivery || hasDeliveryFlatFee">Free delivery</Checkbox>

                        </Col>

                        <Col :span="4" :class="['clearfix']">

                            <div :class="['single-draggable-item-toolbox', 'float-right']">

                                <!-- Remove Destination Button  -->
                                <Icon type="ios-trash-outline" class="single-draggable-item-icon mr-2" size="20" @click="handleConfirmRemoveDestination(index)" />

                                <!-- Move Destination Button  -->
                                <Icon type="ios-move" class="single-draggable-item-icon dragger-handle mr-2" size="20" />
                            
                            </div> 

                        </Col>

                    </Row>

                </draggable>

                <div class="clearfix mt-3">
                    
                    <basicButton type="default" size="default" icon="ios-add" :showIcon="true"
                                 class="float-right" buttonClass="pl-3 pr-2" 
                                 @click.native="addDestination()">
                        <span>Add Destination</span>
                    </basicButton>

                </div>

            </Form>

            <!-- Footer -->
            <template v-slot:footer>
                <div class="clearfix">
                    <Button type="primary" @click.native="handleSubmit()" class="float-right">Done</Button>
                    <Button @click.native="closeModal()" class="float-right mr-2">Cancel</Button>
                </div>
            </template>

        </Modal>
    </div>
</template>
<script>

    import draggable from 'vuedraggable';
    import freeDeliveryAlert from './../freeDeliveryAlert.vue';
    import flatFeeDeliveryAlert from './../flatFeeDeliveryAlert.vue';
    import modalMixin from './../../../../../../../components/_mixins/modal/main.vue';
    import basicButton from './../../../../../../../components/_common/buttons/basicButton.vue';

    export default {
        mixins: [ modalMixin ],
        components: { draggable, freeDeliveryAlert, flatFeeDeliveryAlert, basicButton },
        props: {
            location: {
                type: Object,
                default: null
            },
            currencySymbol: {
                type: String,
                default: null
            },
            freeDelivery: {
                type: Boolean
            },
            hasDeliveryFlatFee: {
                type: Boolean
            },
        },
        data(){
            return {
                locationForm: null
            }
        },
        methods: {
            getlocationForm(){
                
                var location = Object.assign({}, this.location);

                return _.cloneDeep( location );

            },
            addDestination(){

                var destinationNumber = this.locationForm.delivery_destinations.length + 1;

                this.locationForm.delivery_destinations.push({
                    name: 'Destination ' + destinationNumber,
                    allow_free_delivery: false,
                    cost: 0
                });

            },
            handleConfirmRemoveDestination(index){

                const self = this;

                var name = this.locationForm.delivery_destinations[index].name;

                //  Make a popup confirmation modal so that we confirm the destination removal
                this.$Modal.confirm({
                    width: '450',
                    closable: true,
                    okText: 'Delete',
                    cancelText: 'Cancel',
                    title: 'Delete Destination',
                    onOk: () => { this.handleRemoveDestination(index) },
                    render: function (h) {
                        return h(
                            'span', [
                                'Are you sure you want to delete "',
                                h('span', { class: ['font-weight-bold'] }, name),
                                '". After this destination is deleted you cannot recover it again.'
                            ]
                        )
                    }
                });
            },
            handleRemoveDestination(index) {

                //  Remove destination from list
                this.locationForm.delivery_destinations.splice(index, 1);

                //  Destination deleted success message
                this.$Message.success({
                    content: 'Destination removed!',
                    duration: 6
                });

            },
            handleSubmit(){

                var validDestinations = this.locationForm.delivery_destinations.filter((destination) => {
                    if( destination.name ){
                        return destination;
                    }
                });

                //  Notify the parent component of the updated destinations
                this.$emit('updated', validDestinations);

                this.$Message.success({
                    content: 'Destinations updated!',
                    duration: 6
                });

                /** Note the closeModal() method is imported from the
                 *  modalMixin file. It handles the closing process 
                 *  of the modal
                 */
                this.closeModal();

            }
        },
        created(){
            this.locationForm = this.getlocationForm();
        }
    }
</script>