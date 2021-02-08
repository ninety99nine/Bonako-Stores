<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {

            $table->increments('id');

            /*  Cart Info  */
            $table->boolean('active')->nullable()->default(true);
            $table->float('sub_total')->nullable();
            $table->float('coupon_total')->nullable();
            $table->float('sale_discount_total')->nullable();
            $table->float('coupon_and_sale_discount_total')->nullable();
            $table->float('delivery_fee')->nullable();
            $table->float('grand_total')->nullable();
            $table->unsignedTinyInteger('total_items')->nullable();
            $table->unsignedTinyInteger('total_unique_items')->nullable();

            /*  Currency Info  */
            $table->unsignedTinyInteger('currency_id')->default(1);

            /*  Location Info  */
            $table->unsignedInteger('location_id')->nullable();

            /*  Ownership Information  */
            $table->bigInteger('owner_id')->unsigned()->nullable();
            $table->string('owner_type')->nullable();

            /*  Timestamps  */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
