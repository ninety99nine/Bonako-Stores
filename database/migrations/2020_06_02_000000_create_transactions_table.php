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
            $table->string('status_id')->nullable();
            $table->string('type')->nullable();
            $table->char('currency', 3)->default('BWP');
            $table->unsignedTinyInteger('percentage_rate')->nullable();
            $table->float('amount')->default(0);
            $table->string('payment_method_id')->nullable();
            $table->string('description')->nullable();

            /*  Payer Information  */
            $table->unsignedBigInteger('payer_id')->nullable();
            $table->string('payer_mobile_number')->nullable();
            $table->unsignedBigInteger('marked_as_paid_user_id')->nullable();

            /*  Ownership Information  */
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
