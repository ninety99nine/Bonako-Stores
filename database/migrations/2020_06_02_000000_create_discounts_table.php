<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {

            $table->increments('id');

            /*  Discount Details  */    
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_fixed_rate')->nullable()->default(false);
            $table->float('fixed_rate');
            $table->boolean('is_percentage_rate')->nullable()->default(false);
            $table->float('percentage_rate');

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
        Schema::dropIfExists('discounts');
    }
}
