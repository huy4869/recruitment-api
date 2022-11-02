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
        Schema::table('application_users', function (Blueprint $table) {
            $table->dropForeign(['image_id']);
            $table->dropForeign(['desire_city_id']);
            $table->dropForeign(['desire_job_id']);
            $table->dropForeign(['desire_job_work_id']);
            $table->dropForeign(['desire_salary']);
            $table->dropForeign(['experience_year']);
            $table->dropColumn([
                'image_id',
                'achievement_imgs',
                'favorite',
                'skill',
                'experience',
                'knowledge',
                'desire_city_id',
                'desire_job_id',
                'desire_job_work_id',
                'desire_salary',
                'experience_year',
                'home_page_recruiter',
                'noteworthy_things'
            ]);
            $table->unsignedBigInteger('user_id')->after('id');
            $table->unsignedBigInteger('application_id')->after('user_id');
            $table->text('favorite_skill')->nullable()->after('address');
            $table->text('experience_knowledge')->nullable()->after('favorite_skill');
            $table->text('noteworthy')->nullable()->after('motivation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_users', function (Blueprint $table) {
            $table->unsignedBigInteger('image_id')->nullable();
            $table->json('achievement_imgs')->nullable();
            $table->text('favorite')->nullable();
            $table->text('skill')->nullable();
            $table->text('experience')->nullable();
            $table->text('knowledge')->nullable();
            $table->unsignedBigInteger('desire_city_id');
            $table->unsignedBigInteger('desire_job_id');
            $table->unsignedBigInteger('desire_job_work_id');
            $table->unsignedBigInteger('desire_salary');
            $table->unsignedBigInteger('experience_year');
            $table->string('home_page_recruiter', 255)->nullable();
            $table->text('noteworthy_things')->nullable();
            $table->dropColumn(['user_id', 'favorite_skill', 'experience_knowledge', 'noteworthy', 'application_id']);
        });
    }
};
