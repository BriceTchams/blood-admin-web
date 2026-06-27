<?php

// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HopitalAuthController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\SouscriptionController;



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

// Routes ADMIN (avec auth Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/licenses/generate', [LicenseController::class, 'generate']); //generation de license
    Route::post('/licenses/{id}/renew', [LicenseController::class, 'renew']); // renouvellement de license 
    Route::post('/licenses/{id}/revoke', [LicenseController::class, 'revoke']); // suppression de license 
    Route::get('/hopitals/{hopital_id}/licenses', [LicenseController::class, 'listByHopital']); // listing d'hopitaux par license
    Route::get('/admin/licenses', [LicenseController::class, 'dashboard']); 


    //souscription 

    // creation dd'une souscription
      Route::post('/souscriptions', [SouscriptionController::class, 'store']);
      //affichage d'information liee a une soucription
    Route::get('/souscriptions/{id}', [SouscriptionController::class, 'show']);

    //mise a jour des informations liees a une souscription
    Route::put('/souscriptions/{id}', [SouscriptionController::class, 'update']);

    //renouvellemet d'une souscription
    Route::post('/souscriptions/{id}/renew', [SouscriptionController::class, 'renew']);

    //suspension d'une souscription
    Route::post('/souscriptions/{id}/suspend', [SouscriptionController::class, 'suspend']);
    
    // annulation d'une souscription
    Route::post('/souscriptions/{id}/cancel', [SouscriptionController::class, 'cancel']);

    //affichage de soucription avec hopitaux avec concerne

    Route::get('/hopitals/{hopital_id}/souscriptions', [SouscriptionController::class, 'byHopital']);

    //affichage de toutes les soucription
    Route::get('/admin/souscriptions', [SouscriptionController::class, 'dashboard']);
});