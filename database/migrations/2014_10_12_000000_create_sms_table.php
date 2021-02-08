<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->string('mobile_number');
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('error_message')->nullable();
            $table->bigInteger('origin_id')->unsigned()->nullable();
            $table->string('origin_type')->nullable();
            $table->timestamps();

            /*  Indexes  */
            $table->index(['mobile_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
}
