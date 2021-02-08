<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemLinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('item_lines', function (Blueprint $table) {

            $table->increments('id');

            /*  Basic Info  */
            $table->string('name')->nullable();
            $table->string('description', 500)->nullable();
            
            /*  Unit Pricing Info  */
            $table->float('unit_regular_price')->nullable();
            $table->float('unit_sale_price')->nullable();
            $table->float('unit_price')->nullable();
            $table->float('unit_sale_discount')->nullable();

            /*  Total Pricing Info  */
            $table->float('sub_total')->nullable();
            $table->float('sale_discount_total')->nullable();
            $table->float('grand_total')->nullable();

            /*  Quantity Info  */
            $table->unsignedInteger('quantity')->default(0);

            /*  Product Info  */
            $table->unsignedInteger('product_id');

            /*  Ownership Information  */
            $table->unsignedInteger('cart_id');

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('item_lines');
    }
}
