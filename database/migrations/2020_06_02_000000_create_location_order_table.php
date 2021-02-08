<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationOrderTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('location_order', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('location_id');
            $table->boolean('is_shared')->default(false);

            /*  Timestamps  */
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('location_order');
    }
}
