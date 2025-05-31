<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::post('/login', [AuthController::class, 'login']);


// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// // Sesuaikan namespace controller dengan struktur proyek Laravel Anda
// use App\Http\Controllers\AuthController; // Controller Anda yang sudah ada
// use App\Http\Controllers\Api\Employee\AttendanceController as EmployeeAttendanceController;
// use App\Http\Controllers\Api\Employee\ProfileController as EmployeeProfileController;
// use App\Http\Controllers\Api\Employee\ShiftController as EmployeeShiftController;
// use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
// use App\Http\Controllers\Api\Admin\AttendanceController as AdminAttendanceController;
// use App\Http\Controllers\Api\Admin\ShiftController as AdminShiftController;
// use App\Http\Controllers\Api\Admin\BarcodeController as AdminBarcodeController;
// // Tambahkan controller lain jika ada

// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider and all of them will
// | be assigned to the "api" middleware group. Make something great!
// |
// */

// // --- Rute Autentikasi ---
// Route::post('/login', [AuthController::class, 'login'])->name('api.login'); // Rute Anda yang sudah ada

// Route::middleware('auth:sanctum')->group(function () {
//     // Rute Anda yang sudah ada untuk mendapatkan user, bisa dipertahankan atau diubah ke /auth/me
//     Route::get('/user', function (Request $request) {
//         // Anda mungkin ingin memuat relasi di sini juga jika diperlukan oleh Flutter
//         return $request->user()->load(['division', 'jobTitle', 'education']);
//     })->name('api.user.current');

//     // Atau, jika ingin konsisten dengan contoh Flutter sebelumnya:
//     // Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
//     // Pastikan AuthController Anda memiliki method 'me'

//     Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout'); // Rute Logout BARU
// });


// // --- Rute untuk Karyawan (Employee) ---
// Route::middleware('auth:sanctum')->prefix('employee')->name('api.employee.')->group(function () {
//     // Absensi
//     Route::post('/attendance/clock-in', [EmployeeAttendanceController::class, 'clockIn'])->name('attendance.clockin');
//     Route::post('/attendance/clock-out', [EmployeeAttendanceController::class, 'clockOut'])->name('attendance.clockout');
//     Route::get('/attendance/history', [EmployeeAttendanceController::class, 'history'])->name('attendance.history');
//     Route::get('/attendance/today', [EmployeeAttendanceController::class, 'todayAttendance'])->name('attendance.today');

//     // Profil
//     Route::get('/profile', [EmployeeProfileController::class, 'show'])->name('profile.show');
//     // Route::put('/profile', [EmployeeProfileController::class, 'update'])->name('profile.update'); // Jika karyawan bisa update profil

//     // Shift
//     Route::get('/shift/mine', [EmployeeShiftController::class, 'myShift'])->name('shift.mine');
// });


// // --- Rute untuk Admin ---
// // Anda mungkin ingin menambahkan middleware khusus untuk admin di sini,
// // misalnya ->middleware(['auth:sanctum', 'auth.admin'])
// Route::middleware(['auth:sanctum', /* 'auth.admin' */])->prefix('admin')->name('api.admin.')->group(function () {
//     // Manajemen Pengguna (Karyawan)
//     Route::apiResource('users', AdminUserController::class); // Menyediakan endpoint CRUD untuk user

//     // Manajemen Absensi oleh Admin
//     Route::get('attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
//     Route::put('attendances/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendances.update');
//     // Route::post('attendances', [AdminAttendanceController::class, 'store'])->name('attendances.store'); // Jika admin bisa input manual
//     // Route::delete('attendances/{attendance}', [AdminAttendanceController::class, 'destroy'])->name('attendances.destroy');

//     // Manajemen Shift
//     Route::apiResource('shifts', AdminShiftController::class);

//     // Manajemen Barcode/QR Code
//     Route::apiResource('barcodes', AdminBarcodeController::class);

//     // Tambahkan rute lain untuk admin jika diperlukan (misalnya, dashboard data)
//     // Route::get('/dashboard/summary', [AdminDashboardController::class, 'summary'])->name('dashboard.summary');
// });


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sesuaikan namespace controller dengan struktur proyek Laravel Anda
use App\Http\Controllers\AuthController; // Controller Anda yang sudah ada

// Controller untuk Karyawan (User)
use App\Http\Controllers\UserAttendanceController; // Untuk absensi & data terkait user

// Controller untuk Admin
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController; // Mengelola data karyawan
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController; // Mengelola semua absensi
use App\Http\Controllers\Admin\BarcodeController as AdminBarcodeController;

use App\Http\Controllers\Admin\ShiftController as AdminShiftController; // Anda perlu membuat controller ini
use App\Http\Controllers\Admin\MasterDataController as AdminMasterDataController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Rute Autentikasi ---
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    // Mengambil data user yang sedang login
    // Route Anda yang sudah ada:
    // Route::get('/user', function (Request $request) {
    //     return $request->user()->load(['division', 'jobTitle', 'education']);
    // })->name('api.user.current');
    // Atau menggunakan method di AuthController:
    Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me'); // Pastikan ada method 'me'

    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
});


// --- Rute untuk Karyawan (Employee/User) ---
// Menggunakan UserAttendanceController untuk sebagian besar aksi karyawan
Route::middleware('auth:sanctum')->prefix('employee')->name('api.employee.')->group(function () {
    // Absensi
    Route::post('/attendance/clock-in', [UserAttendanceController::class, 'clockIn'])->name('attendance.clockin');
    Route::post('/attendance/clock-out', [UserAttendanceController::class, 'clockOut'])->name('attendance.clockout');
    Route::get('/attendance/history', [UserAttendanceController::class, 'attendanceHistory'])->name('attendance.history'); // Method di UserAttendanceController
    Route::get('/attendance/today', [UserAttendanceController::class, 'todayAttendance'])->name('attendance.today'); // Method di UserAttendanceController

    // Profil (bisa diletakkan di UserAttendanceController atau controller Profile terpisah)
    Route::get('/profile', [UserAttendanceController::class, 'myProfile'])->name('profile.show'); // Method di UserAttendanceController
    // Route::put('/profile', [UserAttendanceController::class, 'updateProfile'])->name('profile.update');

    // Shift (bisa diletakkan di UserAttendanceController atau controller Shift terpisah)
    Route::get('/shift/mine', [UserAttendanceController::class, 'myShift'])->name('shift.mine'); // Method di UserAttendanceController
});


// --- Rute untuk Admin ---
Route::middleware(['auth:sanctum', 'auth.admin'])->prefix('admin')->name('api.admin.')->group(function () {
    // Manajemen Karyawan oleh Admin
    Route::apiResource('employees', AdminEmployeeController::class); // Menggunakan EmployeeController di Admin

    // Manajemen Absensi oleh Admin
    Route::get('attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{attendance}', [AdminAttendanceController::class, 'show'])->name('attendances.show');
    Route::put('attendances/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendances.update');
    // Route::post('attendances', [AdminAttendanceController::class, 'store'])->name('attendances.store');
    // Route::delete('attendances/{attendance}', [AdminAttendanceController::class, 'destroy'])->name('attendances.destroy');

    // Manajemen Barcode oleh Admin
    Route::apiResource('barcodes', AdminBarcodeController::class);

    // Manajemen Shift oleh Admin (menggunakan AdminShiftController yang perlu Anda buat)
    Route::apiResource('shifts', AdminShiftController::class); // Anda perlu membuat AdminShiftController.php

    // Manajemen Master Data lainnya (Divisi, Jabatan, Pendidikan) oleh Admin
    Route::get('master/divisions', [AdminMasterDataController::class, 'getDivisions'])->name('master.divisions');
    Route::get('master/job-titles', [AdminMasterDataController::class, 'getJobTitles'])->name('master.jobtitles');
    Route::get('master/educations', [AdminMasterDataController::class, 'getEducations'])->name('master.educations');
    // Tambahkan rute POST, PUT, DELETE untuk master data jika admin bisa mengelolanya via API
    // Route::apiResource('master/divisions', AdminDivisionController::class); // Jika ingin CRUD lengkap & controller terpisah
});