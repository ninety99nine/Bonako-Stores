<template>

    <FormItem prop="location_ids" :error="serverLocationIdsError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Title -->
            <span :class="['mr-2']">Locations: </span>

            <!-- Select -->
            <Select v-model="productForm.location_ids" multiple :class="['w-100', 'mr-2']"
                    :disabled="isLoadingLocations" :loading="isLoadingLocations">
                <Option v-for="(assignedLocation, index) in assignedLocations" :value="assignedLocation.id"
                        :key="index" :disabled="location.id === assignedLocation.id">
                    {{ assignedLocation.name }}
                </Option>
            </Select>

            <!-- Refresh Button -->
            <div :style="{ width: '40px' }">
                <Poptip trigger="hover" content="Refresh the locations" word-wrap width="300"
                        :style="{ marginTop: '-2px' }">
                    <Button class="p-1" @click.native="fetchProductLocations()">
                        <Icon type="ios-refresh" :size="20" />
                    </Button>
                </Poptip>
            </div>

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
            location: {
                type: Object,
                default: null
            },
            assignedLocations: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            serverErrors: {
                type: Array,
                default: function(){
                    return [];
                }
            },
            parentFetchProductLocations: {
                type: Function,
                default: null
            },
        },
        data(){
            return {
                isLoadingLocations: false
            }
        },
        computed: {
            serverLocationIdsError(){
                return (this.serverErrors || {}).location_ids;
            }
        },
        methods: {
            fetchProductLocations() {
                this.parentFetchProductLocations();
            },
        }
    };

</script>
