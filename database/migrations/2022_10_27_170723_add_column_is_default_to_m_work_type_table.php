<?php

use App\Models\MWorkType;
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
        Schema::table('m_work_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('is_default')->default(MWorkType::IS_DEFAULT)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_work_types', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
