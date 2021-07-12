<template>

    <FormItem prop="arrangement" :error="serverArrangementError" class="mb-2">
        <Poptip trigger="click" width="350" placement="top-end" word-wrap class="poptip-w-100"
                content="Enter position to place this popular store">
            <div class="d-flex">
                <span class="mr-2">Position</span>
                <InputNumber v-model="popularStoreForm.arrangement" class="w-100"
                             placeholder="100" :disabled="isLoading" :min="minimum"
                             @on-blur="onChange()" @on-change="onChange()">
                </InputNumber>
            </div>
        </Poptip>
    </FormItem>

</template>

<script>

    export default {
        props: {
            popularStoreForm: {
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
                minimum: 1
            }
        },
        computed: {
            serverArrangementError(){
                return (this.serverErrors || {}).arrangement;
            }
        },
        methods: {
            onChange(){

                //  Wait for the most updated value of the "arrangement"
                this.$nextTick(() => {

                    if(this.popularStoreForm.arrangement === null || this.popularStoreForm.arrangement === undefined){

                        this.popularStoreForm.arrangement = this.minimum;

                    }

                });

            }
        }
    };

</script>
