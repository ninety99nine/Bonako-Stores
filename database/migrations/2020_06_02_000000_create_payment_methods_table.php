<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {

            $table->increments('id');

            /*  Payment Method Details  */
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('used_online')->default(false);
            $table->boolean('used_offline')->default(false);
            $table->boolean('active')->default(false);

            /*  Timestamps  */
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}
