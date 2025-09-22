<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\FixedScheduleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Admin\AdminReservationController;

/**
 * ===============================
 * AUTH ROUTES (Public)
 * ===============================
 */
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');

Route::middleware('auth:api')->group(function () {
   Route::get('/profile', [ProfileController::class, 'profile'])->name('Profile');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('UpdateProfile');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('Logout');

    /**
     * ===============================
     * ADMIN ROUTES
     * ===============================
     */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Rooms Management
        Route::apiResource('rooms', RoomController::class);

        // Fixed Schedules Management
        Route::apiResource('fixed-schedules', FixedScheduleController::class);

        // Users Management
        Route::apiResource('users', UserController::class);

        // Reservation Approval (khusus admin)
        Route::get('reservations/pending', [AdminReservationController::class, 'indexPending']);
        Route::put('reservations/{id}/approve', [AdminReservationController::class, 'approve'])
            ->name('admin.reservations.approve');
        Route::put('reservations/{id}/reject', [AdminReservationController::class, 'reject'])
            ->name('admin.reservations.reject');
    });

    /**
     * ===============================
     * KARYAWAN ROUTES
     * ===============================
     */
    Route::middleware('role:karyawan')->prefix('karyawan')->group(function () {
        // Reservations (buat & kelola milik sendiri)
        Route::apiResource('reservations', ReservationController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
    });
});
