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

            /*  Basic Info  */
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('code');
            $table->boolean('is_fixed_rate')->nullable()->default(false);
            $table->float('fixed_rate');
            $table->tinyInteger('is_percentage_rate')->nullable()->default(false);
            $table->tinyInteger('percentage_rate');

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
