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
        Schema::table('search_jobs', function (Blueprint $table) {
            $table->dropColumn(['model_class', 'attribute', 'content_type']);
            $table->json('content')->after('user_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_jobs', function (Blueprint $table) {
            $table->string('model_class', 255)->nullable();
            $table->string('attribute', 255)->nullable();
            $table->unsignedTinyInteger('content_type')->nullable();
            $table->text('content')->nullable()->change();
        });
    }
};
