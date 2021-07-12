<template>

    <FormItem prop="duration" :error="serverDurationError" class="mb-2">
        <Poptip trigger="click" width="350" placement="top-end" word-wrap class="poptip-w-100"
                content="Enter the total number of days to list this advert">
            <div class="d-flex">
                <span class="mr-2" :style="{ minWidth: 'max-content' }">Duration (Days)</span>
                <InputNumber v-model="advertForm.duration" class="w-100"
                             placeholder="100" :disabled="isLoading || (isEditing && !advertForm.reset_dates)"
                             :min="minimum" @on-blur="onChange()" @on-change="onChange()">
                </InputNumber>
            </div>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            advertForm: {
                type: Object,
                default: null
            },
            isEditing: {
                type: Boolean,
                default: false
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
                minimum: 1
            }
        },
        computed: {
            serverDurationError(){
                return (this.serverErrors || {}).duration;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "duration"
                this.$nextTick(() => {

                    if(this.advertForm.duration === null || this.advertForm.duration === undefined){

                        this.advertForm.duration = this.minimum;

                    }

                });

            }
        }
    };

</script>
