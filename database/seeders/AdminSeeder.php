<?php

namespace Database\Seeders;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataAdmin = [
            'id' => 1,
            'name' => 'System Admin',
            'email' => 'admin@domain.com',
            'password' => Hash::make('123456xX@'),
            'status' => Admin::STATUS_ACTIVE,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        Schema::disableForeignKeyConstraints();
        Admin::query()->truncate();
        Schema::enableForeignKeyConstraints();

        Admin::query()->insert([$dataAdmin]);
    }
}
