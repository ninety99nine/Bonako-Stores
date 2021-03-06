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
            $table->boolean('active')->default(true);
            $table->float('sub_total')->default(0);
            $table->float('coupon_total')->default(0);
            $table->float('sale_discount_total')->default(0);
            $table->float('coupon_and_sale_discount_total')->default(0);
            $table->boolean('allow_free_delivery')->default(false);
            $table->float('delivery_fee')->default(0);
            $table->float('grand_total')->default(0);
            $table->unsignedTinyInteger('total_items')->default(0);
            $table->unsignedTinyInteger('total_unique_items')->default(0);

            /*  Currency Info  */
            $table->char('currency', 3)->default('BWP');

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
