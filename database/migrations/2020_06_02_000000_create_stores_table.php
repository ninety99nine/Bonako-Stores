<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->increments('id');

            /*  Basic Info  */
            $table->string('name')->nullable();
            $table->boolean('online')->default(false);
            $table->string('offline_message')->nullable();
            $table->boolean('allow_sending_merchant_sms')->default(true);
            $table->char('hex_color', 6)->default('2D8CF0');
            $table->bigInteger('user_id')->unsigned()->nullable();

            /*  Timestamps  */
            $table->timestamps();

            /*  Foreign Keys & Indexes  */
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
