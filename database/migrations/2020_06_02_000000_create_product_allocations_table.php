<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_allocations', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('owner_id');
            $table->string('owner_type');
            $table->tinyInteger('arrangement');
            $table->tinyInteger('quantity');

            /*  Timestamps  */
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('product_allocations');
    }
}
