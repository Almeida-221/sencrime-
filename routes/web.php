<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\TypeInfractionController;
use App\Http\Controllers\InfractionController;
use App\Http\Controllers\AccidentController;
use App\Http\Controllers\AmendeController;
use App\Http\Controllers\ServiceRetribueController;
use App\Http\Controllers\ImmigrationClandestineController;
use App\Http\Controllers\SurveillanceController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\NotificationWebController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TransportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/home', [DashboardController::class, 'index'])->name('home');

// Gestion des utilisateurs
Route::resource('users', UserController::class);

// Gestion des services
Route::resource('services', ServiceController::class);
Route::get('services/{service}/effectifs', [ServiceController::class, 'effectifs'])->name('services.effectifs');

// Gestion des agents
Route::resource('agents', AgentController::class);
Route::post('agents/{agent}/mouvement', [AgentController::class, 'mouvement'])->name('agents.mouvement');

// Types d'infractions
Route::resource('types-infractions', TypeInfractionController::class);

// Gestion des infractions
Route::resource('infractions', InfractionController::class);

// Gestion des accidents
Route::resource('accidents', AccidentController::class);

// Gestion des amendes
Route::resource('amendes', AmendeController::class);
Route::post('amendes/{amende}/paiement', [AmendeController::class, 'paiement'])->name('amendes.paiement');

// Services rétribués
Route::resource('services-retribues', ServiceRetribueController::class);
Route::post('services-retribues/{servicesRetribue}/paiement', [ServiceRetribueController::class, 'paiement'])->name('services-retribues.paiement');

// Immigration clandestine
Route::resource('immigrations', ImmigrationClandestineController::class);

// Transports (historique + surveillance live pour admin/superviseur)
Route::get('/transports', [TransportController::class, 'index'])->name('transports.index');
Route::get('/transports/live-all', [TransportController::class, 'liveAll'])->name('transports.live-all');
Route::get('/transports/{transport}', [TransportController::class, 'show'])->name('transports.show');
Route::get('/transports/{transport}/position', [TransportController::class, 'livePosition'])->name('transports.position');
Route::delete('/transports/{transport}', [TransportController::class, 'destroy'])->name('transports.destroy');
Route::delete('/transports/accident/{accidentId}', [TransportController::class, 'destroyByAccident'])->name('transports.destroy-by-accident');

// Surveillance (carte interactive)
Route::get('/surveillance', [SurveillanceController::class, 'index'])->name('surveillance.index');
Route::get('/surveillance/data', [SurveillanceController::class, 'data'])->name('surveillance.data');

// Import Excel
Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
Route::post('/imports/accidents', [ImportController::class, 'accidents'])->name('imports.accidents');
Route::post('/imports/infractions', [ImportController::class, 'infractions'])->name('imports.infractions');
Route::post('/imports/immigrations', [ImportController::class, 'immigrations'])->name('imports.immigrations');
Route::get('/imports/template/accidents', [ImportController::class, 'templateAccidents'])->name('imports.template.accidents');
Route::get('/imports/template/infractions', [ImportController::class, 'templateInfractions'])->name('imports.template.infractions');
Route::get('/imports/template/immigrations', [ImportController::class, 'templateImmigrations'])->name('imports.template.immigrations');

// Rapports & PDF
Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index');
Route::get('/rapports/pdf', [RapportController::class, 'pdf'])->name('rapports.pdf');

// Chat
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/non-lus', [ChatController::class, 'nonLus'])->name('chat.non-lus');
Route::get('/chat/{conversation}/messages', [ChatController::class, 'messages'])->name('chat.messages');
Route::post('/chat/{conversation}/envoyer', [ChatController::class, 'envoyer'])->name('chat.envoyer');
Route::post('/chat/{conversation}/envoyer-vocal', [ChatController::class, 'envoyerVocal'])->name('chat.envoyer-vocal');
Route::post('/chat/creer-direct', [ChatController::class, 'creerDirect'])->name('chat.creer-direct');
Route::post('/chat/creer-groupe', [ChatController::class, 'creerGroupe'])->name('chat.creer-groupe');
Route::post('/chat/supprimer-messages', [ChatController::class, 'supprimerMessages'])->name('chat.supprimer-messages');

// Notifications web (AJAX)
Route::get('/notifications/ajax', [NotificationWebController::class, 'ajax'])->name('notifications.ajax');
Route::post('/notifications/{id}/lire', [NotificationWebController::class, 'lire'])->name('notifications.lire');
Route::post('/notifications/lire-tout', [NotificationWebController::class, 'lireTout'])->name('notifications.lire-tout');
