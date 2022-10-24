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
        Schema::table('application_user_work_histories', function (Blueprint $table) {
            $table->char('period_start', 6)->nullable()->change();
            $table->char('period_end', 6)->nullable()->change();
            $table->dropColumn('position_offices');
            $table->json('position_office_ids')->nullable()->after('period_end');
        });

        Schema::table('application_user_learning_histories', function (Blueprint $table) {
            $table->char('enrollment_period_start', 6)->nullable()->change();
            $table->char('enrollment_period_end', 6)->nullable()->change();
        });

        Schema::table('application_user_licenses_qualifications', function (Blueprint $table) {
            $table->char('new_issuance_date', 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_user_work_histories', function (Blueprint $table) {
            $table->dateTime('period_start')->change();
            $table->dateTime('period_end')->change();
            $table->dropColumn('position_office_ids');
            $table->text('position_offices')->nullable()->after('period_end');
        });

        Schema::table('application_user_learning_histories', function (Blueprint $table) {
            $table->dateTime('enrollment_period_start')->change();
            $table->dateTime('enrollment_period_end')->change();
        });

        Schema::table('application_user_licenses_qualifications', function (Blueprint $table) {
            $table->date('new_issuance_date')->change();
        });
    }
};
