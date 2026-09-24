<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ሱፐር አድሚን
        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // ብዙ ድርጅቶች (Tenants)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('slug')->unique(); // ለምሳሌ: melasolution
            $table->string('admin_phone')->unique();
            $table->string('admin_pin', 10);
            $table->decimal('latitude', 10, 8)->default(9.030000);
            $table->decimal('longitude', 11, 8)->default(38.740000);
            $table->integer('allowed_radius_meters')->default(100);
            $table->time('work_start_time')->default('08:30:00');
            $table->time('work_end_time')->default('17:00:00');
            $table->enum('status', ['active', 'suspended'])->default('active'); // በ 1-ክሊክ ማገጃ
            $table->date('subscription_expires_at')->nullable();
            $table->timestamps();
        });

        // የማስታወቂያ ሰሌዳ (Ad Network)
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('banner_image'); // የማስታወቂያ ፎቶ
            $table->string('target_url'); // ክሊክ ሲደረግ የሚሄድበት ሊንክ
            $table->enum('placement', ['employee_dashboard', 'admin_dashboard', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('super_admins');
    }
};
