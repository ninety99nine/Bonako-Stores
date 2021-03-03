<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');

            /*  Coupon Details  */
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('always_apply')->default(false);
            $table->boolean('uses_code')->default(false);
            $table->string('code');
            $table->char('currency', 3)->default('BWP');
            $table->boolean('is_fixed_rate')->default(false);
            $table->float('fixed_rate')->default(0);
            $table->tinyInteger('is_percentage_rate')->default(false);
            $table->tinyInteger('percentage_rate')->default(0);

            /*  Ownership Information  */
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
        Schema::dropIfExists('coupons');
    }
}
