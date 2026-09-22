<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // የሁለቱም ካላንደር ቀኖች
            $table->date('date_gc'); // Gregorian Calendar (e.g. 2025-02-18)
            $table->string('date_ec'); // Ethiopian Calendar (e.g. 2017-06-11)
            
            // Check-in መረጃዎች
            $table->timestamp('check_in_at')->nullable();
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_lng', 11, 8)->nullable();
            $table->integer('check_in_distance_meters')->nullable(); // ከድርጅቱ ስንት ሜትር ርቀው እንደነበር
            
            // Check-out መረጃዎች
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_out_lat', 10, 8)->nullable();
            $table->decimal('check_out_lng', 11, 8)->nullable();
            $table->integer('check_out_distance_meters')->nullable();
            
            // ሁኔታ
            $table->enum('status', ['present', 'late', 'absent', 'on_leave'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            // አንድ ሰራተኛ በአንድ ቀን አንድ አቴንዳንስ ብቻ እንዲኖረው
            $table->unique(['employee_id', 'date_gc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
