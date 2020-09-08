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

            /*  Basic Info  */
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('description', 500)->nullable();

            /*  Status  */
            $table->boolean('active')->nullable()->default(false);

            /*  Store Info  */
            $table->unsignedInteger('store_id')->nullable();

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
