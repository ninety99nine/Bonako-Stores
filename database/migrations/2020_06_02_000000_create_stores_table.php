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
            $table->boolean('online')->nullable()->default(false);
            $table->string('offline_message')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            /*  Currency Info  */
            $table->json('currency')->nullable();

            /*  Stock Info  */
            $table->unsignedInteger('minimum_stock_quantity')->default(10);

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
