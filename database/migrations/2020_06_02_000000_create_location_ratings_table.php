<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationRatingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('location_ratings', function (Blueprint $table) {
            $table->increments('id');

            /*  Store Rating Details  */
            $table->tinyInteger('value')->nullable();
            $table->string('comment', 500)->nullable();
            $table->unsignedInteger('user_id');

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
        Schema::dropIfExists('location_ratings');
    }
}
