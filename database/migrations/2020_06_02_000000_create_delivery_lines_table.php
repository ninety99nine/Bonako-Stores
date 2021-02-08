<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryLinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('delivery_lines', function (Blueprint $table) {

            $table->increments('id');

            /*  Basic Info  */
            $table->string('name')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('physical_address')->nullable();
            $table->bigInteger('address_type_id')->unsigned()->nullable();
            $table->char('delivery_type', 1)->default('d');
            $table->string('day')->nullable();
            $table->string('time')->nullable();
            $table->string('destination')->nullable();

            /*  Address Info  */
            $table->unsignedInteger('address_id');

            /*  Ownership Information  */
            $table->unsignedInteger('order_id');

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('delivery_lines');
    }
}
