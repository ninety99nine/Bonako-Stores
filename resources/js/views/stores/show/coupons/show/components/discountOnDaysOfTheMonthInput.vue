<template>

    <FormItem prop="discount_on_days_of_the_month" :error="serverDiscountOnDaysOfTheMonthError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Select -->
            <Poptip trigger="hover" content="Select days of the month that this coupon be activated" word-wrap class="poptip-w-100">
                <Select v-model="couponForm.discount_on_days_of_the_month" filterable multiple :class="['w-100', 'mr-2']"
                        :disabled="!couponForm.allow_discount_on_days_of_the_month || isLoading"  placeholder="Select days of month">
                    <Option v-for="(dayOfTheMonth, index) in daysOfTheMonth" :value="dayOfTheMonth.value" :key="index">{{ dayOfTheMonth.displayName }}</Option>
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
            daysOfTheMonth(){
                var days = _.range(1, 32);

                return days.map((day) => {
                    return {
                        value: day,
                        displayName: (day < 10 ? '0' : '') + day
                    }
                });
            },
            serverDiscountOnDaysOfTheMonthError(){
                return (this.serverErrors || {}).discount_on_days_of_the_month;
            }
        },
    };

</script>
