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
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_cancelled')->default(false);
            $table->string('cancellation_reason')->nullable();
            $table->json('detected_changes')->nullable();

            /*  Unit Pricing Info  */
            $table->char('currency', 3)->default('BWP');
            $table->float('unit_regular_price')->default(0);
            $table->float('unit_sale_price')->default(0);
            $table->float('unit_price')->default(0);
            $table->float('unit_sale_discount')->default(0);

            /*  Total Pricing Info  */
            $table->float('sub_total')->default(0);
            $table->float('sale_discount_total')->default(0);
            $table->float('grand_total')->default(0);

            /*  Quantity Info  */
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('original_quantity')->default(0);

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
