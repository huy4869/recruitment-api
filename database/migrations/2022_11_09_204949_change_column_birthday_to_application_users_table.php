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
            $table->dropColumn('birthday');
        });

        Schema::table('application_users', function (Blueprint $table) {
            $table->date('birthday')->nullable()->after('alias_name');
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
            $table->dropColumn('birthday');
        });

        Schema::table('application_users', function (Blueprint $table) {
            $table->timestamp('birthday')->after('alias_name')->nullable();
        });
    }
};
