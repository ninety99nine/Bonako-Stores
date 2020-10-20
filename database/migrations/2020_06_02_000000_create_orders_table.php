<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');

            /*  Basic Info  */
            $table->string('number')->nullable();
            $table->json('currency')->nullable();
            $table->timestampTz('created_date')->nullable();

            /*  Rating Info  */
            $table->timestampTz('request_customer_rating_at')->nullable();

            /*  Status  */
            $table->string('status')->default('draft');

            /*  Payment Status  */
            $table->string('payment_status')->default('unpaid');

            /*  Fulfillment Status  */
            $table->string('fulfillment_status')->default('unfulfilled');

            /*  Item Info  */
            $table->json('item_lines')->nullable();

            /*  Coupon Info  */
            $table->json('coupon_lines')->nullable();

            /*  Cart Info  */
            $table->float('sub_total')->nullable();
            $table->float('coupon_total')->nullable();
            $table->float('discount_total')->nullable();
            $table->float('coupon_and_discount_total')->nullable();
            $table->float('delivery_fee')->nullable();
            $table->float('grand_total')->nullable();

            /*  Customer Info  */
            $table->unsignedInteger('customer_id')->nullable();
            $table->json('customer_info')->nullable();

            /*  Delivery Info  */
            $table->json('delivery_info')->nullable();

            /*  Checkout Info  */
            $table->string('checkout_method')->nullable();

            /*  Store Info  */
            $table->unsignedInteger('store_id')->nullable();

            /*  Location Info  */
            $table->unsignedInteger('location_id')->nullable();

            /*  Timestamps  */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
