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
        Schema::table('job_postings', function (Blueprint $table) {
            $table->unsignedBigInteger('applies')->default(0)->after('views');
            $table->timestamp('released_at')->after('created_by')->nullable();
            $table->json('job_type_ids')->after('store_id');
            $table->dropForeign(['job_type_id']);
            $table->dropColumn('job_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn(['applies', 'released_at', 'job_type_ids']);
            $table->unsignedBigInteger('job_type_id');

            $table->foreign('job_type_id')->references('id')->on('m_job_types')->onDelete('cascade');
        });
    }
};
