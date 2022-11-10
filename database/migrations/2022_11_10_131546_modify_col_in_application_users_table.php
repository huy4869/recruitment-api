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
        Schema::table('application_users', function (Blueprint $table) {
            $table->string('building', 255)->nullable()->after('address');
            $table->dropColumn(['city']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_users', function (Blueprint $table) {
            $table->string('city', 255)->nullable()->after('province_city_id');
            $table->dropColumn(['building']);
        });
    }
};
