<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');

            /*  Customer Details  */
            $table->unsignedBigInteger('user_id')->nullable();

            /**********************************
             *  CUSTOMER DATA ON CHECKOUT     *
             *********************************/

            $table->unsignedMediumInteger('total_coupons_used_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_instant_carts_used_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_adverts_used_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_orders_placed_by_customer_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_orders_placed_by_store_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_free_delivery_on_checkout')->default(0);

            $table->unsignedMediumInteger('grand_total_on_checkout')->default(0);
            $table->unsignedMediumInteger('sub_total_on_checkout')->default(0);
            $table->unsignedMediumInteger('sale_discount_total_on_checkout')->default(0);
            $table->unsignedMediumInteger('coupon_total_on_checkout')->default(0);

            $table->unsignedMediumInteger('coupon_and_sale_discount_total_on_checkout')->default(0);
            $table->unsignedMediumInteger('delivery_fee_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_items_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_unique_items_on_checkout')->default(0);

            /**********************************
             *  CUSTOMER DATA ON CONVERSION   *
             *********************************/

            $table->unsignedMediumInteger('total_coupons_used_on_conversion')->default(0);
            $table->unsignedMediumInteger('total_instant_carts_used_on_conversion')->default(0);
            $table->unsignedMediumInteger('total_adverts_used_on_conversion')->default(0);
            $table->unsignedMediumInteger('total_orders_placed_by_customer_on_conversion')->default(0);
            $table->unsignedMediumInteger('total_orders_placed_by_store_on_conversion')->default(0);
            $table->unsignedMediumInteger('total_free_delivery_on_conversion')->default(0);

            $table->unsignedMediumInteger('grand_total_on_conversion')->default(0);
            $table->unsignedMediumInteger('sub_total_on_conversion')->default(0);
            $table->unsignedMediumInteger('sale_discount_total_on_conversion')->default(0);
            $table->unsignedMediumInteger('coupon_total_on_conversion')->default(0);

            $table->unsignedMediumInteger('coupon_and_sale_discount_total_on_conversion')->default(0);
            $table->unsignedMediumInteger('delivery_fee_on_conversion')->default(0);
            $table->unsignedMediumInteger('total_items_on_conversion')->default(0);
            $table->unsignedMediumInteger('total_unique_items_on_conversion')->default(0);

            /*  Ownership Information  */
            $table->unsignedBigInteger('location_id')->nullable();

            /*  Timestamps  */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
