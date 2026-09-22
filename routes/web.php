<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\AdminController;

// የመጀመሪያ ገጽ - በቀጥታ ወደ ሰራተኛ መግቢያ ይወስዳል
Route::get('/', function () {
    return redirect()->route('employee.login');
});

// ==========================================
// 1. የሰራተኞች ፖርታል (Employee Portal)
// ==========================================
Route::prefix('portal')->name('employee.')->group(function () {
    Route::get('/login', [EmployeePortalController::class, 'showLogin'])->name('login');
    Route::post('/login', [EmployeePortalController::class, 'login'])->name('login.submit');
    Route::post('/logout', [EmployeePortalController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [EmployeePortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/check-in', [EmployeePortalController::class, 'checkIn'])->name('checkin');
    Route::post('/check-out', [EmployeePortalController::class, 'checkOut'])->name('checkout');
    Route::post('/leave-request', [EmployeePortalController::class, 'submitLeave'])->name('leave.submit');
});

// ==========================================
// 2. የአድሚን መቆጣጠሪያ (Admin Portal)
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employee.store');
    Route::post('/geofence', [AdminController::class, 'updateGeofence'])->name('geofence.update');
    Route::post('/leave/{id}/status', [AdminController::class, 'updateLeaveStatus'])->name('leave.status');
    Route::post('/announcements', [AdminController::class, 'postAnnouncement'])->name('announcement.post');
    Route::get('/export-attendance', [AdminController::class, 'exportAttendanceCsv'])->name('attendance.export');
});
