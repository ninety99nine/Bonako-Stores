<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->increments('id');

            /*  Subscription Package Details  */
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->string('frequency');
            $table->unsignedSmallInteger('duration');
            $table->float('price');

            /*  Timestamps  */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}
