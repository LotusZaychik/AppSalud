<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\EmergencyContactController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\MedicalHistoryController;

// Ruta de prueba para verificar conectividad
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now(),
    ]);
});

// Ruta para generar token de prueba (temporal)
Route::post('/generate-test-token', function (Request $request) {
    try {
        $user = \App\Models\User::find($request->user_id ?? 1);
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        
        $token = $user->createToken('test-token')->plainTextToken;
        
        return response()->json([
            'message' => 'Token generado correctamente',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email']),
            'timestamp' => now(),
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Ruta de prueba para appointments sin autenticación
Route::post('/test-appointment', function (Request $request) {
    return response()->json([
        'message' => 'Prueba de endpoint appointments exitosa',
        'received_data' => $request->all(),
        'timestamp' => now(),
    ]);
});

// Ruta de prueba con autenticación para debugging
Route::middleware('auth:sanctum')->post('/test-auth', function (Request $request) {
    return response()->json([
        'message' => 'Autenticación exitosa',
        'user' => $request->user(),
        'token_found' => $request->bearerToken() ? 'YES' : 'NO',
        'headers' => $request->headers->all(),
        'timestamp' => now(),
    ]);
});

// Ruta de prueba para profile sin autenticación (debugging)
Route::get('/test-profile', function () {
    try {
        $user = \App\Models\User::with('persona')->first();
        if (!$user) {
            return response()->json(['error' => 'No hay usuarios en la base de datos'], 404);
        }
        
        return response()->json([
            'message' => 'Usuario encontrado',
            'user' => $user,
            'has_persona' => $user->persona ? 'YES' : 'NO',
            'persona_data' => $user->persona,
            'timestamp' => now(),
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Ruta de prueba para login sin middleware
Route::post('/test-login', function (Request $request) {
    return response()->json([
        'message' => 'Test login funcionando',
        'received_data' => $request->only(['email', 'password']),
        'headers' => $request->headers->all(),
        'csrf_token' => csrf_token(),
        'timestamp' => now(),
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil',  [AuthController::class, 'perfil']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rutas para perfil de usuario
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/change-password', [UserController::class, 'changePassword']);
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);
    
    // Rutas para medicamentos
    Route::apiResource('medications', MedicationController::class);
    
    // Rutas para citas médicas
    Route::apiResource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/complete', [AppointmentController::class, 'markAsCompleted']);
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'markAsCancelled']);
    
    // Rutas para contactos de emergencia
    Route::apiResource('emergency-contacts', EmergencyContactController::class);
    
    // Rutas para historial médico automático
    Route::apiResource('medical-history', MedicalHistoryController::class);
    Route::get('/medical-history-recent', [MedicalHistoryController::class, 'recent']);
});


