<?php
// app/Http/Controllers/Api/HopitalAuthController.php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Hopital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Db;

use Illuminate\Validation\ValidationException;
use App\Http\Requests\Api\HopitalLoginRequest;
use App\Http\Requests\Api\HopitalRegisterRequest;

class HopitalAuthController
{
    /**
     * 🔓 LOGIN : Authentifier un hôpital
     * POST /api/hopitals/auth/login
     */
    public function login(HopitalLoginRequest $request)
    {
        $validated = $request->validated();

        //  Trouver l'hôpital par code
        $hopital = Hopital::byCode($validated['hopital_code'])
            ->active()
            ->with('user')
            ->firstOrFail();

        //  Récupérer l'utilisateur associé
        $user = $hopital->user;

        //  Vérifier le mot de passe
        if (!Hash::check($validated['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'password' => ['Mot de passe incorrect.'],
            ]);
        }

        //  Vérifier la validité de la licence
        if (!$hopital->isLicenseValid()) {
            return response()->json([
                'success' => false,
                'message' => 'License expired. Please contact administrator.',
            ], 403);
        }

        //  Créer le token API (Sanctum)
        $token = $user->createToken('hopital-app', ['hopital:' . $hopital->id])
            ->plainTextToken;

        //  Mettre à jour le statut de sync
        $hopital->update([
            'last_synced_at' => now(),
            'sync_statut' => 'synced',
        ]);

        //  Réponse complète
        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'role' => $user->role,
                'telephone' => $user->telephone,
            ],
            'hopital' => [
                'id' => $hopital->id,
                'nom' => $hopital->nom,
                'code' => $hopital->code_hopital,
                'ville' => $hopital->ville,
                'statut' => $hopital->statut,
                'license_expires_at' => $hopital->license_expires_at,
            ],
            // ← CRUCIAL pour l'offline
            'password_hash' => $user->password_hash,
            'token_expires_at' => now()->addDays(30),
        ], 200);
    }

    /**
     * ✏️ REGISTER : Admin crée un nouvel hôpital + utilisateur
     * POST /api/hopitals/auth/register
     */
    public function register(HopitalRegisterRequest $request)
    {
        $validated = $request->validated();

        //  Transaction : créer User + Hopital atomiquement
        $user = DB::transaction(function () use ($validated) {
            // Créer l'utilisateur
            $user = User::create([
                'type' => 'hopital',
                'login' => $validated['login'],
                'password_hash' => Hash::make($validated['password']),
                'role' => 'user',
                'telephone' => $validated['telephone'] ?? null,
                'uuid' => \Illuminate\Support\Str::uuid(),
                'sync_statut' => 'pending',
            ]);

            // Créer l'hôpital associé
            Hopital::create([
                'user_id' => $user->id,
                'nom' => $validated['nom'],
                'ville' => $validated['ville'],
                'adresse' => $validated['adresse'] ?? null,
                'email' => $validated['email'] ?? null,
                'telephone_principal' => $validated['telephone'] ?? null,
                'code_hopital' => $this->generateHopitalCode(),
                'statut' => 'active',
                'license_expires_at' => now()->addDays(30), // Trial 30 jours
                'sync_statut' => 'pending',
            ]);

            return $user;
        });

        return response()->json([
            'success' => true,
            'message' => 'Hôpital créé avec succès',
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
            ],
            'hopital' => $user->hopital,
        ], 201);
    }

    /**
     * ✅ VERIFY : Vérifier le token actuel
     * GET /api/hopitals/auth/verify
     */
    public function verify(Request $request)
    {
        $user = $request->user();
        $hopital = $user->hopital;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'role' => $user->role,
            ],
            'hopital' => [
                'id' => $hopital->id,
                'nom' => $hopital->nom,
                'code' => $hopital->code_hopital,
            ],
            'token' => $user->currentAccessToken()->plainTextToken,
            'password_hash' => $user->password_hash,
            'expires_at' => now()->addDays(30),
        ], 200);
    }

    /**
     * 🚪 LOGOUT : Révoquer le token
     * POST /api/hopitals/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ], 200);
    }

    /**
     * 👤 GET PROFILE : Récupérer le profil complet
     * GET /api/hopitals/profile
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $hopital = $user->hopital;

        return response()->json([
            'success' => true,
            'user' => $user,
            'hopital' => $hopital,
        ], 200);
    }

    /**
     * ✏️ UPDATE PROFILE : Mettre à jour le profil
     * PUT /api/hopitals/profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'telephone' => 'nullable|string',
            'nom' => 'nullable|string',
            'adresse' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $user = $request->user();
        $hopital = $user->hopital;

        // Mettre à jour l'utilisateur
        if (isset($validated['telephone'])) {
            $user->update(['telephone' => $validated['telephone']]);
        }

        // Mettre à jour l'hôpital
        $hopital->update(array_filter([
            'nom' => $validated['nom'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'email' => $validated['email'] ?? null,
            'sync_statut' => 'pending',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour',
            'hopital' => $hopital,
        ], 200);
    }

    /**
     * 🔧 Générer un code unique pour l'hôpital
     */
    private function generateHopitalCode(): string
    {
        $prefix = 'HOSP-';
        $suffix = strtoupper(\Illuminate\Support\Str::random(6));
        $code = $prefix . $suffix;

        // Vérifier l'unicité
        while (Hopital::where('code_hopital', $code)->exists()) {
            $suffix = strtoupper(\Illuminate\Support\Str::random(6));
            $code = $prefix . $suffix;
        }

        return $code;
    }
}