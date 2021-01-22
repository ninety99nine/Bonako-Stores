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
            width="600"
            v-model="modalVisible"
            title="Manage Destinations"
            @on-visible-change="detectClose">

            <!-- Form -->
            <Form ref="locationForm" :model="locationForm">

                <!-- Flat Fee Desclaimer-->
                <Alert v-if="locationForm.delivery_flat_fee != null" type="info" class="my-3">
                    <span class="font-weight-bold">Note:</span>
                    <span v-if="locationForm.delivery_flat_fee == 0">Delivery to any destination will be <span class="font-weight-bold text-success">Free Delivery</span></span>
                    <span v-else>Delivery to any destination will be charged {{ store.currency.symbol + locationForm.delivery_flat_fee }}</span>
                </Alert>

                <Row :gutter="10" class="mb-2">

                    <Col :span="10">

                        <span class="font-weight-bold">Destination Name</span>

                    </Col>

                    <Col :span="14">

                        <span class="font-weight-bold">Delivery Cost ({{ store.currency.symbol }})</span>

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
                        :key="index" class="delivery-destination mb-2">

                        <Col :span="10">

                            <Input type="text" v-model="destination.name" placeholder="Name" 
                                    @keyup.enter.native="handleSubmit()">
                            </Input>

                        </Col>

                        <Col :span="10">

                            <!-- Enter Cost -->
                            <InputNumber v-model.number="destination.cost" class="w-100" placeholder="40"
                                        :disabled="(locationForm.delivery_flat_fee != null)"
                                        @keyup.enter.native="handleSubmit()">
                            </InputNumber>

                        </Col>

                        <Col :span="4">

                            <div class="single-draggable-item-toolbox">

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

    
    import basicButton from './../../../../../components/_common/buttons/basicButton.vue';
    import modalMixin from './../../../../../components/_mixins/modal/main.vue';
    import draggable from 'vuedraggable';

    export default {
        mixins: [ modalMixin ],
        components: { basicButton, draggable },
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
        data(){

            return {
                locationForm: null
            }
        },
        computed: {  
            
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
                    cost: null
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