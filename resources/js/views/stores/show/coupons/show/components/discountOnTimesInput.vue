<template>

    <FormItem prop="discount_on_times" :error="serverDiscountOnTimesError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Select -->
            <Poptip trigger="hover" content="Select times that this coupon be activated" word-wrap class="poptip-w-100">
                <Select v-model="couponForm.discount_on_times" filterable multiple :class="['w-100', 'mr-2']"
                        :disabled="!couponForm.allow_discount_on_times || isLoading" placeholder="Select times of day">
                    <Option v-for="(hourOfTheDay, index) in hoursOfTheDay" :value="hourOfTheDay.value" :key="index">{{ hourOfTheDay.displayName }}</Option>
                </Select>
            </Poptip>

        </div>

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

            }
        },
        computed: {
            hoursOfTheDay(){
                var hours = _.range(0, 24);

                return hours.map((hour) => {
                    return {
                        value: (hour < 10 ? '0' : '') + hour,
                        displayName: (hour < 10 ? '0' : '') + (hour + ':00 ') + (hour < 12 ? 'am' : 'pm')
                    }
                });
            },
            serverDiscountOnTimesError(){
                return (this.serverErrors || {}).discount_on_times;
            }
        },
    };

</script>
