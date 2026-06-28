<?php

// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HopitalAuthController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\SouscriptionController;
use App\Http\Controllers\Api\SyncController;




// Routes publiques (sans auth)
Route::prefix('hopitals')->group(function () {
    Route::post('/auth/login', [HopitalAuthController::class, 'login']);
    Route::post('/auth/register', [HopitalAuthController::class, 'register']);
});

// Routes protégées (avec Sanctum)
Route::middleware('auth:sanctum')->prefix('hopitals')->group(function () {
    Route::get('/auth/verify', [HopitalAuthController::class, 'verify']);
    Route::post('/auth/logout', [HopitalAuthController::class, 'logout']);
    Route::get('/profile', [HopitalAuthController::class, 'getProfile']);
    Route::put('/profile', [HopitalAuthController::class, 'updateProfile']);
});

//  fonctionnalite qui concerne la licence

// Route PUBLIQUE (vérification)
Route::get('/licenses/verify', [LicenseController::class, 'verify']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/licenses/generate', [LicenseController::class, 'generate']); //generation de license

    Route::post('/licenses/{id}/renew', [LicenseController::class, 'renew']); // renouvellement de license 

    Route::post('/licenses/{id}/revoke', [LicenseController::class, 'revoke']); // suppression de license 

    Route::get('/hopitals/{hopital_id}/licenses', [LicenseController::class, 'listByHopital']); // listing d'hopitaux par license
    
    Route::get('/admin/licenses', [LicenseController::class, 'dashboard']); 


        //souscription 

    // creation d'une souscription
      Route::post('/souscriptions', [SouscriptionController::class, 'store']);

      //affichage d'information liee a une soucription
    Route::get('/souscriptions/{id}', [SouscriptionController::class, 'show']);

    //mise a jour des infos d'une souscription
    Route::put('/souscriptions/{id}', [SouscriptionController::class, 'update']);

    //renouvellemet d'une souscription
    Route::post('/souscriptions/{id}/renew', [SouscriptionController::class, 'renew']);

    //suspension d'une souscription
    Route::post('/souscriptions/{id}/suspend', [SouscriptionController::class, 'suspend']);

    // annulation d'une souscription
    Route::post('/souscriptions/{id}/cancel', [SouscriptionController::class, 'cancel']);

    //affichage de soucription avec hopitaux 

    Route::get('/hopitals/{hopital_id}/souscriptions', [SouscriptionController::class, 'byHopital']);

    //affichage de toutes les souscriptions
    Route::get('/admin/souscriptions', [SouscriptionController::class, 'dashboard']);



        // synchronisation de donnees 


   Route::post('/push', [SyncController::class, 'push']);

    Route::get('/pull', [SyncController::class, 'pull']);

    Route::get('/history', [SyncController::class, 'history']);

    Route::get('/queue', [SyncController::class, 'queue']);

    Route::get('/status', [SyncController::class, 'status']);

    Route::post('/force', [SyncController::class, 'forceSync']);

});