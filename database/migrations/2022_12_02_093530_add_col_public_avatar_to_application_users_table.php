<?php

use App\Models\User;
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
            $table->unsignedTinyInteger('is_public_avatar')
                ->default(User::STATUS_PUBLIC_AVATAR)
                ->after('noteworthy');
            $table->unsignedTinyInteger('is_public_thumbnail')
                ->default(User::STATUS_PUBLIC_AVATAR)
                ->after('is_public_avatar');
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
            $table->dropColumn(['is_public_avatar', 'is_public_thumbnail']);
        });
    }
};
