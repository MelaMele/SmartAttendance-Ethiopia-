<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type'); // 'ህመም', 'አስቸኳይ', 'ዓመታዊ', 'ሌላ'
            $table->date('start_date_gc');
            $table->string('start_date_ec');
            $table->date('end_date_gc');
            $table->string('end_date_ec');
            $table->text('reason');
            $table->string('attachment')->nullable(); // ማስረጃ ፋይል (Medical/Letter)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
