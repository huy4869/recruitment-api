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
        Schema::table('m_position_offices', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_default')->default(1)->after('name');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_position_offices', function (Blueprint $table) {
            $table->dropColumn(['is_default', 'created_by']);
        });
    }
};
