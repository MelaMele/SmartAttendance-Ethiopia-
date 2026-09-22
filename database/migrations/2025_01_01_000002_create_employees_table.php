<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone_number')->unique(); // ለመግቢያ የሚያገለግል
            $table->string('access_code', 10)->unique(); // 6 ወይም 8 አሃዝ ሚስጥር ቁጥር
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('device_id')->nullable(); // ስልካቸውን ሎክ ለማድረግ (Anti-fraud)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
