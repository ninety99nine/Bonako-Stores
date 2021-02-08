<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {

            $table->increments('id');

            /*  Basic Info  */
            $table->string('name')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('physical_address')->nullable();
            $table->bigInteger('address_type_id')->unsigned()->nullable();

            /*  Ownership Information  */
            $table->bigInteger('owner_id')->unsigned()->nullable();
            $table->string('owner_type')->nullable();

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
