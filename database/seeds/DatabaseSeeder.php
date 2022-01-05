<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        /****************************
         *  CLEAR THE CACHE         *
         ***************************/

        \Illuminate\Support\Facades\Artisan::call('cache:clear');

        /****************************
         *  TRUNCATE TABLES         *
         ***************************/

        Schema::disableForeignKeyConstraints(); //  Disable Foreign key checks

        DB::table('addresses')->truncate();
        DB::table('adverts')->truncate();
        DB::table('carts')->truncate();
        DB::table('coupon_allocations')->truncate();
        DB::table('coupon_lines')->truncate();
        DB::table('coupons')->truncate();
        DB::table('customers')->truncate();
        DB::table('delivery_lines')->truncate();
        DB::table('favourites')->truncate();
        DB::table('instant_carts')->truncate();
        DB::table('item_lines')->truncate();
        DB::table('location_order')->truncate();
        DB::table('location_payment_methods')->truncate();
        DB::table('location_ratings')->truncate();
        DB::table('location_user')->truncate();
        DB::table('locations')->truncate();
        DB::table('mobile_verifications')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('orders')->truncate();
        DB::table('permissions')->truncate();
        DB::table('popular_stores')->truncate();
        DB::table('product_allocations')->truncate();
        DB::table('products')->truncate();
        DB::table('reports')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('short_codes')->truncate();
        DB::table('sms')->truncate();
        DB::table('stores')->truncate();
        DB::table('subscriptions')->truncate();
        DB::table('transactions')->truncate();
        DB::table('users')->truncate();
        DB::table('variables')->truncate();

        Schema::enableForeignKeyConstraints();  //  Enable on Foreign key checks

    }
}
