<?php

use App\Models\FeedbackJob;
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
        Schema::table('feedback_jobs', function (Blueprint $table) {
            $table->unsignedTinyInteger('be_read')->after('content')->default(FeedbackJob::NOT_READ);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback_jobs', function (Blueprint $table) {
            $table->dropColumn('be_read');
        });
    }
};
