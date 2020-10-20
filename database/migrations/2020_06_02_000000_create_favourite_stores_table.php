<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavouriteStoresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('favourite_stores', function (Blueprint $table) {
            $table->increments('id');

            /*  Favourite Stores Details  */
            $table->unsignedInteger('user_id')->nullable();
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
        Schema::dropIfExists('favourite_stores');
    }
}
