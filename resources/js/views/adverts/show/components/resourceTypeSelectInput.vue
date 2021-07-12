<template>

    <FormItem prop="resource_type" :error="serverResourceTypeError" :class="['mb-2']">

        <div class="d-flex">

            <span class="mr-2" :style="{ minWidth: 'max-content' }">Action</span>

            <!-- Select -->
            <Select v-model="advertForm.resource_type" :class="['w-100']" @on-select="onSelect"
                    :disabled="isLoading" placeholder="Select resource type">
                <Option v-for="(resourceType, index) in resourceTypes" :key="index"
                        :value="resourceType.value" :label="resourceType.name">
                </Option>
            </Select>

        </div>

    </FormItem>

</template>

<script>

    export default {
        props: {
            advertForm: {
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
            }
        },
        data(){
            return {
                resourceTypes: [
                    {
                        name: 'Visit store',
                        value: 'store'
                    },
                    {
                        name: 'Visit location',
                        value: 'location'
                    },
                    {
                        name: 'Visit instant cart',
                        value: 'instant_cart'
                    },
                ]
            }
        },
        computed: {
            serverResourceTypeError(){
                return (this.serverErrors || {}).resource_type;
            }
        },
        methods: {
            onSelect(){

                //  Reset resource id
                this.advertForm.resource_id = null;

            }
        },
    };

</script>
