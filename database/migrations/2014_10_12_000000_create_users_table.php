<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile_number')->unique()->nullable();
            $table->timestamp('mobile_number_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('account_type')->default('basic');
            $table->boolean('accepted_terms_and_conditions')->default(false);
            $table->rememberToken();
            $table->timestamps();

            /*  Indexes  */
            $table->index(['email']);
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
        Schema::dropIfExists('users');
    }
}
