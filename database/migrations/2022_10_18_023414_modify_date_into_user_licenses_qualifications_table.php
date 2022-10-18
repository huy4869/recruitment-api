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
        Schema::table('user_licenses_qualifications', function (Blueprint $table) {
            $table->char('new_issuance_date', 6)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_licenses_qualifications', function (Blueprint $table) {
            $table->dateTime('new_issuance_date')->change();
        });
    }
};
