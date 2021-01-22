<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');

            /*  Transaction Details  */
            $table->string('number')->nullable();
            $table->string('type')->nullable();
            $table->float('amount');
            $table->string('payment_method_id');
            $table->string('description')->nullable();

            /*  Ownership Information  */
            $table->bigInteger('user_id')->unsigned();

            /*  Timestamps  */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
