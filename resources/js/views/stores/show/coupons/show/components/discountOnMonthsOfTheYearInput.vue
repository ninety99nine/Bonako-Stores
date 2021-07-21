<template>

    <FormItem prop="discount_on_months_of_the_year" :error="serverDiscountOnMonthsOfTheYearError" :class="['mb-2']">

        <div class="d-flex">

            <!-- Select -->
            <Poptip trigger="hover" content="Select months of the year that this coupon be activated" word-wrap class="poptip-w-100">
                <Select v-model="couponForm.discount_on_months_of_the_year" filterable multiple :class="['w-100', 'mr-2']"
                        :disabled="!couponForm.allow_discount_on_months_of_the_year || isLoading" placeholder="Select months of year">
                    <Option v-for="(month, index) in monthsOfTheYear" :value="month" :key="index">{{ month }}</Option>
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
                monthsOfTheYear: [
                    'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
                ],
            }
        },
        computed: {
            serverDiscountOnMonthsOfTheYearError(){
                return (this.serverErrors || {}).discount_on_months_of_the_year;
            }
        },
    };

</script>
