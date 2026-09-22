<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\CompanySetting;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ነባሪ የድርጅት ጂፒኤስ መረጃ (Mela Solution - አዲስ አበባ)
        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Mela Solution',
                'latitude' => 9.030000,
                'longitude' => 38.740000,
                'allowed_radius_meters' => 100,
                'work_start_time' => '08:30:00',
                'work_end_time' => '17:00:00',
            ]
        );

        // 2. የሙከራ ሰራተኛ
        Employee::updateOrCreate(
            ['phone_number' => '0911223344'],
            [
                'full_name' => 'አበበ ከበደ',
                'access_code' => '123456',
                'department' => 'Software Engineering',
                'position' => 'Senior Developer',
                'is_active' => true,
            ]
        );
    }
}
