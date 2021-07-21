<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstantCartsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('instant_carts', function (Blueprint $table) {

            $table->increments('id');

            /*  Status  */
            $table->boolean('active')->default(false);

            /*  Basic Info  */
            $table->string('name')->nullable();
            $table->string('description', 500)->nullable();

            /*  Stock Management  */
            $table->boolean('allow_stock_management')->default(false);
            $table->unsignedInteger('stock_quantity')->default(0);

            /*  Location Info  */
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
        Schema::dropIfExists('instant_carts');
    }
}
