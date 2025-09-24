<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\FixedScheduleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\ReservationController;

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
     * ROUTE YANG BISA DIAKSES ADMIN & KARYAWAN
     * ===============================
     */
    Route::middleware('role:admin|karyawan')->group(function () {
        // Rooms (lihat)
        Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');

        // Fixed Schedules (lihat)
        Route::get('fixed-schedules', [FixedScheduleController::class, 'index'])->name('fixed-schedules.index');
        Route::get('fixed-schedules/{id}', [FixedScheduleController::class, 'show'])->name('fixed-schedules.show');

        // Reservations (lihat index & detail → otomatis beda hasil kalau admin atau karyawan)
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show');
    });

    /**
     * ===============================
     * ADMIN ROUTES (Full Kontrol)
     * ===============================
     */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Rooms CRUD
        Route::post('rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
        Route::put('rooms/{id}', [RoomController::class, 'update'])->name('admin.rooms.update');
        Route::delete('rooms/{id}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

        // Fixed Schedules CRUD
        Route::post('fixed-schedules', [FixedScheduleController::class, 'store'])->name('admin.fixed-schedules.store');
        Route::put('fixed-schedules/{id}', [FixedScheduleController::class, 'update'])->name('admin.fixed-schedules.update');
        Route::delete('fixed-schedules/{id}', [FixedScheduleController::class, 'destroy'])->name('admin.fixed-schedules.destroy');

        // Users Management
        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/{id}', [UserController::class, 'show'])->name('admin.users.show');
        Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
        Route::put('users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Reservations kontrol admin
        Route::put('reservations/{id}/approve', [ReservationController::class, 'update'])->name('admin.reservations.approve');
        Route::put('reservations/{id}/reject', [ReservationController::class, 'update'])->name('admin.reservations.reject');
        Route::delete('reservations/{id}', [ReservationController::class, 'destroy'])->name('admin.reservations.destroy');
    });

    /**
     * ===============================
     * KARYAWAN ROUTES (Akses Khusus Karyawan)
     * ===============================
     */
    Route::middleware('role:karyawan')->prefix('karyawan')->group(function () {
        // Reservations (buat & cancel)
        Route::post('reservations', [ReservationController::class, 'store'])->name('karyawan.reservations.store');
        Route::put('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('karyawan.reservations.cancel');
    });
});
