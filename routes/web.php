<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperAdminController;
use App\Models\Ad;

Route::get('/', function () {
    return redirect()->route('employee.login');
});

// የማስታወቂያ ክሊክ መቁጠሪያ (Ad Click Tracker)
Route::get('/ad-click/{id}', function ($id) {
    $ad = Ad::findOrFail($id);
    $ad->increment('clicks_count');
    return redirect()->away($ad->target_url);
})->name('ad.click');

// የዳታቤዝ ማሻሻያ ማስነሻ
Route::get('/setup-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate --force');
        return "✅ የ SmartStaff ዳታቤዝ ማይግሬሽን፣ ሱፐር አድሚን እና የማስታወቂያ ሰሌዳ በተሳካ ሁኔታ ተፈጥረዋል!";
    } catch (\Exception $e) {
        return "❌ ስህተት፡ " . $e->getMessage();
    }
});

// ==========================================
// 1. የሱፐር አድሚን ፖርታል (Super Admin SaaS)
// ==========================================
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/companies', [SuperAdminController::class, 'storeCompany'])->name('company.store');
    Route::post('/companies/{id}/toggle', [SuperAdminController::class, 'toggleCompanyStatus'])->name('company.toggle');
    Route::post('/ads', [SuperAdminController::class, 'storeAd'])->name('ad.store');
    Route::post('/ads/{id}/toggle', [SuperAdminController::class, 'toggleAdStatus'])->name('ad.toggle');
    Route::post('/ads/{id}/delete', [SuperAdminController::class, 'deleteAd'])->name('ad.delete');
});

// ==========================================
// 2. የሰራተኞች ፖርታል (Employee Portal)
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
// 3. የአድሚን መቆጣጠሪያ (Admin Portal)
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employee.store');
    Route::post('/employees/{id}/update', [AdminController::class, 'updateEmployee'])->name('employee.update');
    Route::post('/employees/{id}/delete', [AdminController::class, 'deleteEmployee'])->name('employee.delete');
    
    Route::post('/attendance/{id}/status', [AdminController::class, 'quickStatusUpdate'])->name('attendance.status');
    Route::post('/attendance/{id}/early-leave', [AdminController::class, 'approveEarlyLeave'])->name('early.approve');
    
    Route::post('/geofence', [AdminController::class, 'updateGeofence'])->name('geofence.update');
    Route::post('/leave/{id}/status', [AdminController::class, 'updateLeaveStatus'])->name('leave.status');
    Route::post('/announcements', [AdminController::class, 'postAnnouncement'])->name('announcement.post');
    Route::get('/export-attendance-monthly', [AdminController::class, 'exportMonthlySummaryCsv'])->name('attendance.export.monthly');
});
