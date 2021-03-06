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

            /*  Status Info  */
            $table->string('status_id')->nullable();
            $table->string('payment_status_id')->nullable();
            $table->string('delivery_status_id')->nullable();

            /*  Cancellation Reason  */
            $table->string('cancellation_reason')->nullable();

            /*  Customer Info  */
            $table->unsignedInteger('customer_id')->nullable();

            /*  Delivery Info  */
            $table->string('delivery_confirmation_code')->nullable();
            $table->boolean('delivery_verified')->default(false);
            $table->timestamp('delivery_verified_at')->nullable();

            /*  Rating Info  */
            $table->timestamp('request_customer_rating_at')->nullable();

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
