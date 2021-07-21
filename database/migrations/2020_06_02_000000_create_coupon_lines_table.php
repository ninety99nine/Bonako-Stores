<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponLinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('coupon_lines', function (Blueprint $table) {
            $table->increments('id');

            /*  Coupon Details  */
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('apply_discount')->default(false);
            $table->char('activation_type', 1)->default(1);
            $table->string('code')->nullable();
            $table->boolean('allow_free_delivery')->default(false);
            $table->char('currency', 3)->default('BWP');

            $table->char('discount_rate_type', 1)->default('p');
            $table->float('fixed_rate')->default(0);
            $table->tinyInteger('percentage_rate')->default(0);

            $table->tinyInteger('allow_discount_on_minimum_total')->default(false);
            $table->tinyInteger('discount_on_minimum_total')->default(0);

            $table->tinyInteger('allow_discount_on_total_items')->default(false);
            $table->tinyInteger('discount_on_total_items')->default(2);

            $table->tinyInteger('allow_discount_on_total_unique_items')->default(false);
            $table->tinyInteger('discount_on_total_unique_items')->default(2);

            $table->tinyInteger('allow_discount_on_start_datetime')->default(false);
            $table->timestamp('discount_on_start_datetime')->nullable();

            $table->tinyInteger('allow_discount_on_end_datetime')->default(false);
            $table->timestamp('discount_on_end_datetime')->nullable();

            $table->tinyInteger('allow_usage_limit')->default(false);
            $table->string('usage_limit')->nullable();

            $table->boolean('allow_discount_on_times')->default(false);
            $table->json('discount_on_times')->nullable();
            $table->boolean('allow_discount_on_days_of_the_week')->default(false);
            $table->json('discount_on_days_of_the_week')->nullable();
            $table->boolean('allow_discount_on_days_of_the_month')->default(false);
            $table->json('discount_on_days_of_the_month')->nullable();
            $table->boolean('allow_discount_on_months_of_the_year')->default(false);
            $table->json('discount_on_months_of_the_year')->nullable();
            $table->boolean('allow_discount_on_new_customer')->default(false);
            $table->boolean('allow_discount_on_existing_customer')->default(false);

            /*  Coupon Info  */
            $table->unsignedInteger('coupon_id');

            /*  Ownership Information  */
            $table->unsignedInteger('cart_id');

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('coupon_lines');
    }
}
