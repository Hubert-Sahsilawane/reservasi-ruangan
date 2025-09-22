<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\FixedScheduleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Api\Karyawan\ReservationController as KaryawanReservationController;

/**
 * ===============================
 * AUTH ROUTES (Public)
 * ===============================
 */
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

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

        // Reservations Management (Admin full kontrol)
        Route::get('reservations', [AdminReservationController::class, 'index'])->name('admin.reservations.index');
        Route::put('reservations/{id}/approve', [AdminReservationController::class, 'approve'])->name('admin.reservations.approve');
        Route::put('reservations/{id}/reject', [AdminReservationController::class, 'reject'])->name('admin.reservations.reject');
        Route::delete('reservations/{id}', [AdminReservationController::class, 'destroy'])->name('admin.reservations.destroy');
    });

    /**
     * ===============================
     * KARYAWAN ROUTES
     * ===============================
     */
    Route::middleware('role:karyawan')->prefix('karyawan')->group(function () {
        // Reservations (hanya punya sendiri)
        Route::get('reservations', [KaryawanReservationController::class, 'index'])->name('karyawan.reservations.index');
        Route::post('reservations', [KaryawanReservationController::class, 'store'])->name('karyawan.reservations.store');
    });
});
