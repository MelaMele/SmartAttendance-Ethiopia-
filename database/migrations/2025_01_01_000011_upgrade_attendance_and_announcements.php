<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // የኩባንያ ID ወደ ሰራተኞች ማከል
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            }
        });

        // የአቴንዳንስ ቴብልን በወር ማደራጀት
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'eth_month')) {
                $table->integer('eth_month')->default(1); // 1 = መስከረም, ... 13 = ጳጉሜን
                $table->integer('eth_year')->default(2017);
            }
            if (!Schema::hasColumn('attendances', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            }
        });

        // የመልእክቶች ቴብልን በወር ማደራጀት
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'eth_month')) {
                $table->integer('eth_month')->default(1);
                $table->integer('eth_year')->default(2017);
            }
            if (!Schema::hasColumn('announcements', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
