<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->boolean('uses_code')->nullable()->default(false);
            $table->string('code');
            $table->boolean('is_fixed_rate')->nullable()->default(false);
            $table->float('fixed_rate');
            $table->tinyInt('is_percentage_rate')->nullable()->default(false);
            $table->tinyInteger('percentage_rate');

            /*  Ownership Information  */
            $table->unsignedInteger('store_id')->nullable();

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
