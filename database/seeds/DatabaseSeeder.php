<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);

        //  Truncate tables

        DB::table('adverts')->truncate();
        DB::table('favourites')->truncate();
        DB::table('popular_stores')->truncate();

        DB::table('stores')->truncate();
        DB::table('locations')->truncate();
        DB::table('location_user')->truncate();

        DB::table('orders')->truncate();
        DB::table('item_lines')->truncate();
        DB::table('coupon_lines')->truncate();
        DB::table('delivery_lines')->truncate();
        DB::table('location_order')->truncate();

        DB::table('coupons')->truncate();
        DB::table('coupon_allocations')->truncate();

        DB::table('carts')->truncate();
        DB::table('instant_carts')->truncate();


        DB::table('products')->truncate();
        DB::table('variables')->truncate();
        DB::table('product_allocations')->truncate();

        DB::table('sms')->truncate();
        DB::table('short_codes')->truncate();
        DB::table('subscriptions')->truncate();
        DB::table('transactions')->truncate();
        DB::table('reports')->truncate();

    }
}
