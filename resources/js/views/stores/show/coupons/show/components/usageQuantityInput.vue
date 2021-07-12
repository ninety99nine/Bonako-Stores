<template>

    <FormItem prop="usage_quantity" :error="serverUsageQuantityError" class="mb-2">
        <Poptip trigger="hover" width="350" placement="top-end" word-wrap class="poptip-w-100"
                content="This is the total number of times that this coupon has been used by customers. This value can be reset back to Zero (0).">
            <div class="d-flex">
                <span class="mr-2">Used</span>
                <InputNumber v-model="couponForm.usage_quantity" class="w-100"
                            placeholder="100" :disabled="true" :min="0">
                </InputNumber>
                <Button @click.native="toggleReset()" :type="isReset ? 'primary' : 'default'"
                        :class="['px-2', 'ml-1']">
                    <span v-if="isReset">Undo Reset</span>
                    <span v-else>Reset</span>
                </Button>
            </div>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            couponForm: {
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
                isReset: false,
                original_value: this.couponForm.usage_quantity
            }
        },
        computed: {
            serverUsageQuantityError(){
                return (this.serverErrors || {}).usage_quantity;
            }
        },
        methods: {
            toggleReset(){

                //  If we reset the value to zero
                if( this.isReset ){

                    //  Undo the reset
                    this.couponForm.usage_quantity = this.original_value;

                }else{

                    //  Reset to zero
                    this.couponForm.usage_quantity = 0;

                }

                //  Toggle reset status
                this.isReset = !this.isReset;

            }
        }
    };

</script>
