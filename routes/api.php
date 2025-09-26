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
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile.detail');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('auth.logout');

    /**
     * ===============================
     * ROUTE YANG BISA DIAKSES ADMIN & KARYAWAN
     * ===============================
     */
    Route::middleware('role:admin|karyawan')->group(function () {
        // Rooms
        Route::get('rooms', [RoomController::class, 'index'])->name('rooms.list');
        Route::get('rooms/{id}', [RoomController::class, 'show'])->name('rooms.detail');

        // Fixed Schedules
        Route::get('fixed-schedules', [FixedScheduleController::class, 'index'])->name('fixed-schedules.list');
        Route::get('fixed-schedules/{id}', [FixedScheduleController::class, 'show'])->name('fixed-schedules.detail');

        // Reservations
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.list');
        Route::get('reservations/{id}', [ReservationController::class, 'show'])->name('reservations.detail');
    });

    /**
     * ===============================
     * ADMIN ROUTES
     * ===============================
     */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Rooms
        Route::post('rooms', [RoomController::class, 'store'])->name('rooms.create');
        Route::put('rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.delete');

        // Fixed Schedules
        Route::post('fixed-schedules', [FixedScheduleController::class, 'store'])->name('fixed-schedules.create');
        Route::put('fixed-schedules/{id}', [FixedScheduleController::class, 'update'])->name('fixed-schedules.update');
        Route::delete('fixed-schedules/{id}', [FixedScheduleController::class, 'destroy'])->name('fixed-schedules.delete');

        // Users
        Route::get('users', [UserController::class, 'index'])->name('users.list');
        Route::get('users/{id}', [UserController::class, 'show'])->name('users.detail');
        Route::post('users', [UserController::class, 'store'])->name('users.create');
        Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.delete');

        // Reservations (approve/reject/delete)
        Route::put('reservations/{id}/approve', [ReservationController::class, 'update'])->name('reservations.approve');
        Route::put('reservations/{id}/reject', [ReservationController::class, 'update'])->name('reservations.reject');
        Route::delete('reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.delete');
    });

    /**
     * ===============================
     * KARYAWAN ROUTES
     * ===============================
     */
    Route::middleware('role:karyawan')->prefix('karyawan')->group(function () {
        // Reservations (buat & cancel)
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.create');
        Route::put('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });
});
