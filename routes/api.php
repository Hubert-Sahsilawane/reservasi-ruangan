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
    // Profile
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    /**
     * ===============================
     * ADMIN ROUTES
     * ===============================
     */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Rooms Management (Admin full CRUD)
        Route::apiResource('rooms', RoomController::class);

        // Fixed Schedules Management (Admin full CRUD)
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
        // Rooms (Karyawan cuma bisa lihat)
        Route::get('rooms', [RoomController::class, 'index'])->name('karyawan.rooms.index');
        Route::get('rooms/{id}', [RoomController::class, 'show'])->name('karyawan.rooms.show');

        // Fixed Schedules (Karyawan cuma bisa lihat)
        Route::get('fixed-schedules', [FixedScheduleController::class, 'index'])->name('karyawan.fixed-schedules.index');
        Route::get('fixed-schedules/{id}', [FixedScheduleController::class, 'show'])->name('karyawan.fixed-schedules.show');

        // Reservations (Karyawan bisa buat & lihat punya sendiri)
        Route::get('reservations', [KaryawanReservationController::class, 'index'])->name('karyawan.reservations.index');
        Route::post('reservations', [KaryawanReservationController::class, 'store'])->name('karyawan.reservations.store');
    });
});
