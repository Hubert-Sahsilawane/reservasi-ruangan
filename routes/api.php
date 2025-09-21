<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controller\Api\Auth\LoginController;
use App\Http\Controller\Api\Auth\RegisterController;
use App\Http\Controller\Api\RoomController;
use App\Http\Controller\Api\ReservationController;
use App\Http\Controller\Api\FixedScheduleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Auth routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes (but manual, not apiResource)
Route::middleware('auth:api')->group(function () {
    // Rooms
    Route::get('/rooms', [RoomController::class, 'index']);       // list semua room
    Route::post('/rooms', [RoomController::class, 'store']);      // tambah room
    Route::get('/rooms/{id}', [RoomController::class, 'show']);   // detail 1 room
    Route::put('/rooms/{id}', [RoomController::class, 'update']); // update room
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy']); // hapus room

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);

    // Fixed Schedules
    Route::get('/fixed-schedules', [FixedScheduleController::class, 'index']);
    Route::post('/fixed-schedules', [FixedScheduleController::class, 'store']);
    Route::get('/fixed-schedules/{id}', [FixedScheduleController::class, 'show']);
    Route::put('/fixed-schedules/{id}', [FixedScheduleController::class, 'update']);
    Route::delete('/fixed-schedules/{id}', [FixedScheduleController::class, 'destroy']);
});
