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
            $table->dropColumn('stations');
            $table->json('station_ids')->after('address');
            $table->string('postal_code', 8)->nullable()->change();
            $table->unsignedBigInteger('views')->default(0)->change();
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
            $table->dropColumn('station_ids');
            $table->json('stations')->after('address');
            $table->string('postal_code', 255)->nullable()->change();
            $table->unsignedBigInteger('views')->change();
        });
    }
};
