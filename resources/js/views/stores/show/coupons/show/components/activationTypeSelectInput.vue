<template>

    <FormItem prop="activation_type" :error="serverActivationTypeError" :class="['mb-2']">

        <!-- Select -->
        <Select v-model="couponForm.activation_type" :class="['w-100']"
                :disabled="isLoading" placeholder="Select activation type">
            <Option v-for="(activationType, index) in activationTypes" :key="index"
                    :value="activationType.value" :label="activationType.name">
            </Option>
        </Select>

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
            }
        },
        data(){
            return {
                activationTypes: [
                    {
                        name: 'Always activate',
                        value: 'always apply'
                    },
                    {
                        name: 'Activate with code',
                        value: 'use code'
                    }
                ]
            }
        },
        computed: {
            serverActivationTypeError(){
                return (this.serverErrors || {}).activation_type;
            }
        }
    };

</script>
