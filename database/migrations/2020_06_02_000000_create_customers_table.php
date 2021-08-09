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

            $table->unsignedMediumInteger('total_coupons_used_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_instant_carts_used_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_adverts_used_on_checkout')->default(0);
            $table->unsignedMediumInteger('total_orders_placed_by_customer')->default(0);
            $table->unsignedMediumInteger('total_orders_placed_by_store')->default(0);

            $table->unsignedMediumInteger('checkout_grand_total')->default(0);
            $table->unsignedMediumInteger('checkout_sub_total')->default(0);
            $table->unsignedMediumInteger('checkout_coupons_total')->default(0);
            $table->unsignedMediumInteger('checkout_sale_discount_total')->default(0);

            $table->unsignedMediumInteger('checkout_coupons_and_sale_discount_total')->default(0);
            $table->unsignedMediumInteger('checkout_delivery_fee')->default(0);
            $table->unsignedMediumInteger('total_free_delivery_on_checkout')->default(0);
            $table->unsignedMediumInteger('checkout_total_items')->default(0);
            $table->unsignedMediumInteger('checkout_total_unique_items')->default(0);

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
