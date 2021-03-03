<template>

    <FormItem label="Flat fee" prop="delivery_flat_fee" :error="serverDeliveryFlatFeeError" class="mb-0">
        <Poptip trigger="click" width="350" placement="top-start" word-wrap
                content="This is a flat fee charged for delivery to any destination">
            <InputNumber v-model="locationForm.delivery_flat_fee" size="small" class="w-100"
                         placeholder="100" :disabled="isLoading || locationForm.allow_free_delivery" 
                         :min="0" @on-blur="onChange()" @on-change="onChange()">
            </InputNumber>
        </Poptip>
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

            }
        },
        computed: {
            serverDeliveryFlatFeeError(){
                return (this.serverErrors || {}).delivery_flat_fee;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "delivery_flat_fee"
                this.$nextTick(() => {

                    if(this.locationForm.delivery_flat_fee === null || this.locationForm.delivery_flat_fee === undefined){

                        this.locationForm.delivery_flat_fee = 0;

                    }

                });

            }
        }
    };

</script>
