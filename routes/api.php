<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccidentApiController;
use App\Http\Controllers\Api\InfractionApiController;
use App\Http\Controllers\Api\ImmigrationApiController;
use App\Http\Controllers\Api\DemandeTransportApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\NotificationApiController;

// Authentification publique
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/verify-phone', [AuthController::class, 'verifyPhone']);
Route::post('/auth/setup-pin',    [AuthController::class, 'setupPin']);
Route::post('/auth/login-pin',    [AuthController::class, 'loginPin']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/change-pin',       [AuthController::class, 'changePin']);
    Route::post('/auth/change-telephone', [AuthController::class, 'changeTelephone']);
    Route::put('/auth/fcm-token',         [AuthController::class, 'updateFcmToken']);

    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // Accidents
    Route::get('/types-accidents', [AccidentApiController::class, 'typesAccidents']);
    Route::get('/accidents', [AccidentApiController::class, 'index']);
    Route::post('/accidents', [AccidentApiController::class, 'store']);
    Route::get('/accidents/{id}', [AccidentApiController::class, 'show']);
    Route::post('/accidents/{id}', [AccidentApiController::class, 'update']);   // POST pour multipart
    Route::delete('/accidents/{id}', [AccidentApiController::class, 'destroy']);
    Route::post('/accidents/{id}/transport', [AccidentApiController::class, 'demanderTransport']);

    // Infractions
    Route::get('/types-infractions', [InfractionApiController::class, 'typesInfractions']);
    Route::get('/infractions', [InfractionApiController::class, 'index']);
    Route::post('/infractions', [InfractionApiController::class, 'store']);
    Route::get('/infractions/{id}', [InfractionApiController::class, 'show']);
    Route::put('/infractions/{id}', [InfractionApiController::class, 'update']);
    Route::delete('/infractions/{id}', [InfractionApiController::class, 'destroy']);

    // Immigrations
    Route::get('/immigrations', [ImmigrationApiController::class, 'index']);
    Route::post('/immigrations', [ImmigrationApiController::class, 'store']);
    Route::get('/immigrations/{id}', [ImmigrationApiController::class, 'show']);
    Route::put('/immigrations/{id}', [ImmigrationApiController::class, 'update']);
    Route::delete('/immigrations/{id}', [ImmigrationApiController::class, 'destroy']);

    // Demandes Transport (Transporteur / Ambulance)
    Route::get('/transports', [DemandeTransportApiController::class, 'index']);
    Route::get('/transports/{id}', [DemandeTransportApiController::class, 'show']);
    Route::post('/transports/{id}/accepter', [DemandeTransportApiController::class, 'accepter']);
    Route::post('/transports/{id}/en-cours', [DemandeTransportApiController::class, 'enCours']);
    Route::post('/transports/{id}/terminer', [DemandeTransportApiController::class, 'terminer']);
    Route::post('/transports/{id}/annuler', [DemandeTransportApiController::class, 'annuler']);
    Route::post('/transports/{id}/position', [DemandeTransportApiController::class, 'positionTransporteur']);
    Route::get('/transports/{id}/position',  [DemandeTransportApiController::class, 'getPosition']);

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/notifications/count', [NotificationApiController::class, 'count']);
    Route::post('/notifications/{id}/lire', [NotificationApiController::class, 'lire']);
    Route::post('/notifications/lire-tout', [NotificationApiController::class, 'lireTout']);
});
