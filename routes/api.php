<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\FixedScheduleController;
use App\Http\Controllers\Api\User\UserController;

// Auth routes
Route::post('/login', [LoginController::class, 'login'])->name('Login');
Route::post('/register', [RegisterController::class, 'register'])->name('Register');

Route::middleware('auth:api')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('Me');

    /**
     * Admin-only routes
     */
    Route::middleware('role:admin')->group(function () {
        // Manage Rooms (CRUD)
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');
        Route::put('/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        // Manage FixedSchedules
        Route::get('/fixed-schedules', [FixedScheduleController::class, 'index'])->name('fixedSchedules.index');
        Route::post('/fixed-schedules', [FixedScheduleController::class, 'store'])->name('fixedSchedules.store');
        Route::get('/fixed-schedules/{id}', [FixedScheduleController::class, 'show'])->name('fixedSchedules.show');
        Route::put('/fixed-schedules/{id}', [FixedScheduleController::class, 'update'])->name('fixedSchedules.update');
        Route::delete('/fixed-schedules/{id}', [FixedScheduleController::class, 'destroy'])->name('fixedSchedules.destroy');

        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });

    /**
     * Karyawan routes
     */
    Route::middleware('role:karyawan')->group(function () {
        // Reservation akses untuk karyawan
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::put('/reservations/{id}', [ReservationController::class, 'update'])->name('reservations.update');
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    });
});
