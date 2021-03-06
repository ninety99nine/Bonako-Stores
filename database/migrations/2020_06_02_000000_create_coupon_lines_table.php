<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponLinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('coupon_lines', function (Blueprint $table) {
            $table->increments('id');

            /*  Coupon Details  */
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('always_apply')->default(false);
            $table->boolean('uses_code')->default(false);
            $table->string('code');
            $table->boolean('allow_free_delivery')->default(false);
            $table->char('currency', 3)->default('BWP');

            $table->char('discount_rate_type', 1)->default('p');
            $table->float('fixed_rate')->default(0);
            $table->tinyInteger('percentage_rate')->default(0);

            $table->tinyInteger('allow_discount_on_minimum_total')->default(false);
            $table->tinyInteger('discount_on_minimum_total')->default(0);

            $table->tinyInteger('allow_discount_on_total_items')->default(false);
            $table->tinyInteger('discount_on_total_items')->default(2);

            $table->tinyInteger('allow_discount_on_total_unique_items')->default(false);
            $table->tinyInteger('discount_on_total_unique_items')->default(2);

            /*  Coupon Info  */
            $table->unsignedInteger('coupon_id');

            /*  Ownership Information  */
            $table->unsignedInteger('cart_id');

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('coupon_lines');
    }
}
