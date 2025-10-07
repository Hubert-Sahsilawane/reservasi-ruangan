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
use App\Http\Controllers\Api\DashboardController;

/**
 * ===============================
 * AUTH ROUTES (Public)
 * ===============================
 */
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');

// Dashboard (statistik umum)
Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

/**
 * ===============================
 * PROTECTED ROUTES (Butuh Token)
 * ===============================
 */
Route::middleware('auth:api')->group(function () {

    // Profil Pengguna
    Route::get('/me', [ProfileController::class, 'profile'])->name('profile.detail');
    Route::put('/me', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('auth.logout');

    /**
     * ===============================
     * ADMIN & KARYAWAN ROUTES
     * ===============================
     */
    Route::middleware('role:admin|karyawan')->group(function () {
        /**
         * Rooms
         * Filter & Pagination tersedia
         * Params: ?search=...&status=aktif&per_page=...
         */
        Route::get('rooms', [RoomController::class, 'index'])->name('rooms.list');
        Route::get('rooms/{id}', [RoomController::class, 'show'])->name('rooms.detail');

        /**
         * Fixed Schedules
         * Filter & Pagination tersedia
         * Params: ?search=...&room_id=...&tanggal=...&per_page=...
         */
        Route::get('fixed-schedules', [FixedScheduleController::class, 'index'])->name('fixed-schedules.index');
        Route::get('fixed-schedules/{id}', [FixedScheduleController::class, 'show'])->name('fixed-schedules.detail');

        /**
         * Reservations
         * Filter & Pagination tersedia
         * Params: ?status=approved&tanggal=2025-10-07&per_page=...
         */
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.list');
        Route::get('reservations/{id}', [ReservationController::class, 'show'])->name('reservations.detail');
    });

    /**
     * ===============================
     * ADMIN ROUTES
     * ===============================
     */
    Route::middleware('role:admin')->group(function () {

        /** Rooms (CRUD) */
        Route::post('rooms', [RoomController::class, 'store'])->name('rooms.create');
        Route::put('rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.delete');

        /** Fixed Schedules (CRUD) */
        Route::post('fixed-schedules', [FixedScheduleController::class, 'store'])->name('fixed-schedules.create');
        Route::put('fixed-schedules/{id}', [FixedScheduleController::class, 'update'])->name('fixed-schedules.update');
        Route::delete('fixed-schedules/{id}', [FixedScheduleController::class, 'destroy'])->name('fixed-schedules.delete');

        /** Users (CRUD) */
        Route::get('users', [UserController::class, 'index'])->name('users.list');
        Route::get('users/{id}', [UserController::class, 'show'])->name('users.detail');
        Route::post('users', [UserController::class, 'store'])->name('users.create');
        Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.delete');

        /** Reservations (approve/reject/delete) */
        Route::put('reservations/{id}', [ReservationController::class, 'update'])->name('reservations.update');
        Route::delete('reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.delete');
    });

    /**
     * ===============================
     * KARYAWAN ROUTES
     * ===============================
     */
    Route::middleware('role:karyawan')->prefix('karyawan')->group(function () {
        /** Reservations (buat & cancel) */
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.create');
        Route::put('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });
});
