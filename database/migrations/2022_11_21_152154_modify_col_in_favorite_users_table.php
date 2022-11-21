<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favorite_users', function (Blueprint $table) {
            $table->dropColumn(['favorite_ids']);
            $table->unsignedBigInteger('favorite_user_id')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('favorite_users', function (Blueprint $table) {
            $table->dropColumn(['favorite_user_id']);
            $table->json('favorite_ids')->after('user_id')->nullable();
        });
    }
};
