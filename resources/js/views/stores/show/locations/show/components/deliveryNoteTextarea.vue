<template>

    <FormItem prop="delivery_note" :error="serverDeliveryNoteError" class="mb-0">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100" content="Enter location delivery notice or announcement">
            <div class="d-flex">
                <span :class="['max-content-width', 'mr-2']">Delivery Notice: </span>
                <Input type="textarea" v-model="locationForm.delivery_note" placeholder="Enter delivery notice or announcement"
                       :disabled="isLoading" maxlength="140" show-word-limit class="w-100">
                </Input>
            </div>
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
            serverDeliveryNoteError(){
                return (this.serverErrors || []).delivery_note;
            }
        }
    };

</script>
