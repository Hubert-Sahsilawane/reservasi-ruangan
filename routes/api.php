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
        // Rooms Management (CRUD manual)
        Route::get('rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
        Route::get('rooms/{id}', [RoomController::class, 'show'])->name('admin.rooms.show');
        Route::post('rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
        Route::put('rooms/{id}', [RoomController::class, 'update'])->name('admin.rooms.update');
        Route::delete('rooms/{id}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

        // Fixed Schedules Management (CRUD manual)
        Route::get('fixed-schedules', [FixedScheduleController::class, 'index'])->name('admin.fixed-schedules.index');
        Route::get('fixed-schedules/{id}', [FixedScheduleController::class, 'show'])->name('admin.fixed-schedules.show');
        Route::post('fixed-schedules', [FixedScheduleController::class, 'store'])->name('admin.fixed-schedules.store');
        Route::put('fixed-schedules/{id}', [FixedScheduleController::class, 'update'])->name('admin.fixed-schedules.update');
        Route::delete('fixed-schedules/{id}', [FixedScheduleController::class, 'destroy'])->name('admin.fixed-schedules.destroy');

        // Users Management (CRUD manual)
        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/{id}', [UserController::class, 'show'])->name('admin.users.show');
        Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

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

        // Reservations (Karyawan bisa buat, lihat, detail, dan cancel miliknya)
        Route::get('reservations', [KaryawanReservationController::class, 'index'])->name('karyawan.reservations.index');
        Route::get('reservations/{id}', [KaryawanReservationController::class, 'show'])->name('karyawan.reservations.show');
        Route::post('reservations', [KaryawanReservationController::class, 'store'])->name('karyawan.reservations.store');
        Route::put('reservations/{id}/cancel', [KaryawanReservationController::class, 'cancel'])->name('karyawan.reservations.cancel');
    });
});
