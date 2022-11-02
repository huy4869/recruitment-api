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
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->after('user_id')->nullable();
            $table->string('email', 255)->nullable()->change();
            $table->string('name', 255)->nullable()->change();
            $table->string('tel', 15)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('store_id');
            $table->string('email', 255)->change();
            $table->string('name', 255)->change();
            $table->string('tel', 15)->change();
        });
    }
};
