<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {

            $table->increments('id');

            /*  Location Details  */
            $table->string('name')->nullable();
            $table->string('abbreviation', 10)->nullable();
            $table->string('about_us', 500)->nullable();
            $table->string('contact_us', 500)->nullable();
            $table->string('call_to_action')->nullable();
            $table->boolean('online')->nullable()->default(false);
            $table->string('offline_message')->nullable();
            $table->boolean('allow_delivery')->nullable()->default(false);
            $table->string('delivery_note', 500)->nullable();
            $table->float('delivery_flat_fee')->nullable();
            $table->json('delivery_destinations')->nullable();
            $table->json('delivery_days')->nullable();
            $table->json('delivery_times')->nullable();
            $table->boolean('allow_pickups')->nullable()->default(false);
            $table->string('pickup_note', 500)->nullable();
            $table->json('pickup_destinations')->nullable();
            $table->json('pickup_days')->nullable();
            $table->json('pickup_times')->nullable();
            $table->boolean('allow_payments')->nullable()->default(false);
            $table->json('online_payment_methods')->nullable();
            $table->json('offline_payment_methods')->nullable();
            $table->unsignedTinyInteger('currency_id')->default(1);
            $table->unsignedInteger('orange_money_merchant_code')->nullable();
            $table->unsignedInteger('minimum_stock_quantity')->default(10);
            $table->boolean('allow_sending_merchant_sms')->nullable()->default(true);

            /*  Ownership Information  */
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('store_id')->nullable();

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
