<template>

    <FormItem prop="discount_on_days_of_the_week" :error="serverDiscountOnDaysOfTheWeekError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Select -->
            <Poptip trigger="hover" content="Select days of the week that this coupon be activated" word-wrap class="poptip-w-100">
                <Select v-model="couponForm.discount_on_days_of_the_week" filterable multiple :class="['w-100', 'mr-2']"
                        :disabled="!couponForm.allow_discount_on_days_of_the_week || isLoading" placeholder="Select days of week">
                    <Option v-for="(day, index) in daysOfTheWeek" :value="day" :key="index">{{ day }}</Option>
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
                daysOfTheWeek: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            }
        },
        computed: {
            serverDiscountOnDaysOfTheWeekError(){
                return (this.serverErrors || {}).discount_on_days_of_the_week;
            }
        },
    };

</script>
