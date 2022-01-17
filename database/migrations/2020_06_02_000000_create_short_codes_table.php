<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShortCodesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('short_codes', function (Blueprint $table) {

            $table->increments('id');

            /*  Short Code Details  */
            $table->string('code')->nullable();
            $table->string('action')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->unsignedBigInteger('user_id');

            /*  Reservation Information  */
            $table->unsignedInteger('reserved_for_user_id');

            /*  Ownership Information  */
            $table->unsignedInteger('owner_id');
            $table->string('owner_type');

            /*  Indexes  */
            $table->index('code');

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('short_codes');
    }
}
