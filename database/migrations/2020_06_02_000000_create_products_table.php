<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');

            /*  Product Management  */
            $table->string('name')->nullable();
            $table->string('description', 500)->nullable();
            $table->boolean('show_description')->default(false);
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->boolean('visible')->default(true);
            $table->unsignedTinyInteger('product_type_id')->default(1);

            /*  Variation Management  */
            $table->boolean('allow_variants')->default(false);
            $table->json('variant_attributes')->nullable();

            /*  Pricing Management  */
            $table->boolean('is_free')->default(false);
            $table->char('currency', 3)->default('BWP');
            $table->float('unit_regular_price')->default(0);
            $table->float('unit_sale_price')->default(0);
            $table->float('unit_cost')->default(0);

            /*  Quantity Management  */
            $table->boolean('allow_multiple_quantity_per_order')->default(true);
            $table->boolean('allow_maximum_quantity_per_order')->default(true);
            $table->unsignedTinyInteger('maximum_quantity_per_order')->default(5);

            /*  Stock Management  */
            $table->boolean('allow_stock_management')->default(false);
            $table->boolean('auto_manage_stock')->default(true);
            $table->unsignedInteger('stock_quantity')->default(0);

            /*  Ownership Management  */
            $table->unsignedInteger('parent_product_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();

            /*  Timestamps  */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
