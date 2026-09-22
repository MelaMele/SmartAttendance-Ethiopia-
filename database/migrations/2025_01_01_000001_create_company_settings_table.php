<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Mela Solution');
            $table->decimal('latitude', 10, 8)->default(9.030000); // የድርጅቱ ኬክሮስ (ለምሳሌ አዲስ አበባ)
            $table->decimal('longitude', 11, 8)->default(38.740000); // የድርጅቱ ኬንትሮስ
            $table->integer('allowed_radius_meters')->default(100); // 100 ሜትር
            $table->time('work_start_time')->default('08:30:00'); // የስራ መግቢያ ሰዓት
            $table->time('work_end_time')->default('17:00:00'); // የስራ መውጫ ሰዓት
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
