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
        Schema::table('application_user_learning_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('job_posting_id');
            $table->unsignedBigInteger('application_id')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_user_learning_histories', function (Blueprint $table) {
            $table->dropColumn('application_id');
            $table->unsignedBigInteger('job_posting_id');
        });
    }
};
