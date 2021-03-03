<template>

    <FormItem prop="allow_variants" :error="serverAllowVariantsError" class="mb-4">

        <div class="d-flex">

            <!-- Title -->
            <span :style="{ width: '150px' }" class="d-block font-weight-bold mr-2">Allow Variations: </span>

            <!-- Poptip -->
            <Poptip trigger="hover" width="380" placement="top-start" word-wrap>

                <template slot="content">
                    <span class="d-block">Add variables if this product/service comes in multiple versions e.g) different sizes, materials or colors. You can change the price and information for each variable</span>

                    <span v-if="!isEditing"
                            style="margin-top: -15px;"
                            class="border-top d-block pt-2">
                        <span class="font-weight-bold text-primary">Note: Only available when editing this product after you create it</span>
                    </span>
                </template>

                <!-- Switch -->
                <i-Switch
                    true-color="#13ce66"
                    false-color="#ff4949"
                    class="ml-1" size="large"
                    :value="productForm.allow_variants"
                    :disabled="!isEditing || isLoading"
                    @on-change="productForm.allow_variants = $event">
                    <span slot="open">Yes</span>
                    <span slot="close">No</span>
                </i-Switch>

            </Poptip>
        </div>

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
            isEditing: {
                type: Boolean,
                default: false
            },
            productHasChanged: {
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
            serverAllowVariantsError(){
                return (this.serverErrors || {}).allow_variants;
            }
        }
    };

</script>
