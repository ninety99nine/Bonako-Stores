<template>

    <FormItem prop="pickup_note" :error="serverPickupNoteError" class="mb-2">
        <Poptip trigger="focus" placement="top" word-wrap class="poptip-w-100" content="Enter pickup notice or announcement e.g Pickups only between 9am - 4pm">
            <div class="d-flex">
                <span :class="['max-content-width', 'mr-2']">Pickup Notice: </span>
                <Input type="textarea" v-model="locationForm.pickup_note" placeholder="Enter pickup notice or announcement"
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
            serverPickupNoteError(){
                return (this.serverErrors || []).pickup_note;
            }
        }
    };

</script>
