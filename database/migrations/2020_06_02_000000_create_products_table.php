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

            /*  Product Details  */
            $table->string('name')->nullable();
            $table->string('description', 500)->nullable();
            $table->boolean('active')->nullable()->default(false);
            $table->tinyInteger('arrangement')->nullable();
            $table->string('type')->nullable();
            $table->float('cost_per_item')->nullable();
            $table->float('unit_regular_price')->nullable();
            $table->float('unit_sale_price')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('allow_stock_management')->default(1);
            $table->boolean('auto_manage_stock')->default(1);
            $table->json('variant_attributes')->nullable();
            $table->boolean('allow_variants')->default(0);
            $table->boolean('allow_downloads')->default(0);
            $table->boolean('show_on_store')->default(1);
            $table->boolean('is_new')->default(0);
            $table->boolean('is_featured')->default(0);
            $table->unsignedInteger('parent_product_id')->nullable();
            $table->unsignedInteger('store_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
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
