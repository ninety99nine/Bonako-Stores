<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('location_payment_methods', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('payment_method_id');
            $table->boolean('used_online')->default(false);
            $table->boolean('used_offline')->default(false);

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('location_payment_methods');
    }
}
