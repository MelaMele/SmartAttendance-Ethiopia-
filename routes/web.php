<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect()->route('employee.login');
});

// የዳታቤዝ ማሻሻያ (Database Auto-Updater)
Route::get('/setup-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate --force');

        // አዳዲስ ፊልዶች መጨመራቸውን ማረጋገጥ
        if (!Schema::hasColumn('attendances', 'early_leave_reason')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('early_leave_reason')->nullable();
                $table->boolean('early_leave_approved')->nullable();
            });
        }

        if (!Schema::hasColumn('announcements', 'employee_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnDelete();
            });
        }

        \App\Models\CompanySetting::firstOrCreate(
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

        return "✅ የ SmartStaff ዳታቤዝ አዳዲስ ፊልዶች (ቀድሞ መውጫ እና የግል መልእክት) በተሳካ ሁኔታ ታክለዋል!";
    } catch (\Exception $e) {
        return "❌ ስህተት፡ " . $e->getMessage();
    }
});

// 1. የሰራተኞች ፖርታል
Route::prefix('portal')->name('employee.')->group(function () {
    Route::get('/login', [EmployeePortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [EmployeePortalController::class, 'login'])->name('login.submit');
    Route::post('/logout', [EmployeePortalController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [EmployeePortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/check-in', [EmployeePortalController::class, 'checkIn'])->name('checkin');
    Route::post('/check-out', [EmployeePortalController::class, 'checkOut'])->name('checkout');
    Route::post('/leave-request', [EmployeePortalController::class, 'submitLeave'])->name('leave.submit');
});

// 2. የአድሚን መቆጣጠሪያ
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employee.store');
    Route::post('/geofence', [AdminController::class, 'updateGeofence'])->name('geofence.update');
    Route::post('/leave/{id}/status', [AdminController::class, 'updateLeaveStatus'])->name('leave.status');
    Route::post('/attendance/{id}/early-leave', [AdminController::class, 'approveEarlyLeave'])->name('early.approve');
    Route::post('/announcements', [AdminController::class, 'postAnnouncement'])->name('announcement.post');
    Route::get('/export-attendance', [AdminController::class, 'exportAttendanceCsv'])->name('attendance.export');
});
