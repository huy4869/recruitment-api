<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate([
            'role_id' => User::ROLE_ADMIN,
            'email' => 'superadmin@gmail.com',
        ], [
            'role_id' => User::ROLE_ADMIN,
            'first_name' => 'Admin',
            'last_name' => 'Super',
            'furi_first_name' => 'Admin',
            'furi_last_name' => 'Furigana Super',
            'alias_name' => 'Alias Super Admin',
            'tel' => '0987654321',
            'email' => 'superadmin@gmail.com',
            'birthday' => '1969-10-25',
            'gender_id' => 1,
            'line' => 'https://line.com/superadmin',
            'facebook' => 'https://facebook.com/superadmin',
            'instagram' => 'https://instagram.com/superadmin',
            'twitter' => 'https://twitter.com/superadmin',
            'postal_code' => '100-0000',
            'province_id' => '12',
            'address' => '東京人',
            'building' => '〒135-0003 東京都江東区猿江１丁目９−10',
            'password' => Hash::make('1234'),
            'last_login_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
