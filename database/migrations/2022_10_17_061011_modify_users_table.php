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
        Schema::table('users', function (Blueprint $table) {
            $table->string('manager_name', 255)->nullable()->after('alias_name');
            $table->char('founded_year', 6)->nullable()->after('manager_name');
            $table->unsignedBigInteger('capital_stock')->nullable()->after('founded_year');
            $table->string('employee_quantity', 255)->nullable()->after('capital_stock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['manager_name', 'founded_year', 'capital_stock', 'employee_quantity']);
        });
    }
};
