<template>

    <FormItem label="Barcode" prop="barcode" :error="serverBarcodeError" class="mb-3">
        <Poptip trigger="focus" width="380" placement="top-end" word-wrap
                content="Assign a unique barcode to this product to identify it for inventory management">
            <Input type="text" v-model="productForm.barcode" :disabled="isLoading" placeholder="Enter unique barcode" class="w-100"></Input>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            productForm: {
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
            serverBarcodeError(){
                return (this.serverErrors || {}).barcode;
            }
        }
    };

</script>
