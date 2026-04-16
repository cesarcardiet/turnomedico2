<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public Discovery Routes
Route::prefix('patient')->group(function () {
    Route::get('/specialities', [\App\Http\Controllers\Api\PatientController::class, 'specialities']);
    Route::get('/doctors', [\App\Http\Controllers\Api\PatientController::class, 'search']);
    Route::get('/doctors/{id}', [\App\Http\Controllers\Api\PatientController::class, 'doctorDetail']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'roles' => $request->user()->getRoleNames(),
            'doctor_profile' => $request->user()->hasRole('doctor') ? $request->user()->doctorProfile : null,
        ]);
    });

    // Protected Patient Routes
    Route::prefix('patient')->group(function () {
        Route::get('/appointments', [\App\Http\Controllers\Api\PatientController::class, 'appointments']);
        Route::post('/appointments', [\App\Http\Controllers\Api\PatientController::class, 'book']);
    });

    // Doctor Routes
    Route::prefix('doctor')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\DoctorController::class, 'dashboard']);
        Route::get('/appointments', [\App\Http\Controllers\Api\DoctorController::class, 'appointments']);
        Route::patch('/appointments/{id}/status', [\App\Http\Controllers\Api\DoctorController::class, 'updateAppointmentStatus']);
    });

    // Chat Routes
    Route::prefix('chat')->group(function () {
        Route::get('/rooms', [\App\Http\Controllers\Api\ChatController::class, 'rooms']);
        Route::get('/rooms/{roomId}/messages', [\App\Http\Controllers\Api\ChatController::class, 'messages']);
        Route::post('/messages', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
    });
});
