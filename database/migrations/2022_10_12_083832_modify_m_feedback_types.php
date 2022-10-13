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
        Schema::table('m_feedback_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('has_extend')->default(0)->after('name');
            $table->string('placeholder_extend', 225)->nullable()->after('has_extend');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_feedback_types', function (Blueprint $table) {
            $table->dropColumn(['has_extend', 'placeholder_extend']);
        });
    }
};
