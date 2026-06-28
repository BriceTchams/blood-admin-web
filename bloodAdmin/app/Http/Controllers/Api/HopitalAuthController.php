<?php
// app/Http/Controllers/Api/HopitalAuthController.php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Hopital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

        // Trouver l'hôpital par code
        $hopital = Hopital::byCode($validated['hopital_code'])
            ->active()
            ->with('user')
            ->firstOrFail();

        // Récupérer l'utilisateur associé
        $user = $hopital->user;

        // Vérifier le mot de passe
        if (!Hash::check($validated['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'password' => ['Mot de passe incorrect.'],
            ]);
        }

        // Vérifier la validité de la licence
        if (!$hopital->isLicenseValid()) {
            return response()->json([
                'success' => false,
                'message' => 'License expired. Please contact administrator.',
            ], 403);
        }

        // Créer le token API (Sanctum)
        $token = $user->createToken('hopital-app', ['hopital:' . $hopital->id])
            ->plainTextToken;

        // Mettre à jour le statut de sync
        $hopital->update([
            'last_synced_at' => now(),
            'sync_statut' => 'synced',
        ]);

        // Réponse complète
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

        // Transaction : créer User + Hopital atomiquement
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
                'license_expires_at' => now()->addDays(30),
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
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'telephone' => $user->telephone,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
            'hopital' => [
                'id' => $hopital->id,
                'nom' => $hopital->nom,
                'code_hopital' => $hopital->code_hopital,
                'ville' => $hopital->ville,
                'adresse' => $hopital->adresse,
                'email' => $hopital->email,
                'telephone_principal' => $hopital->telephone_principal,
                'logo' => $hopital->logo,
                'statut' => $hopital->statut,
                'license_expires_at' => $hopital->license_expires_at,
                'created_at' => $hopital->created_at,
            ],
        ], 200);
    }

    /**
     * ✏️ UPDATE PROFILE : Mettre à jour TOUS les champs du profil
     * PUT /api/hopitals/profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            // Champs Utilisateur
            'login' => 'nullable|string|min:3|max:255|unique:users,login,' . $request->user()->id,
            'telephone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string',

            // Champs Hôpital
            'nom' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'telephone_principal' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();
        $hopital = $user->hopital;

        try {
            DB::beginTransaction();

            // ========== MISE À JOUR UTILISATEUR ==========
            
            $userUpdate = [];

            // Mettre à jour le login
            if (isset($validated['login'])) {
                $userUpdate['login'] = $validated['login'];
            }

            // Mettre à jour le téléphone
            if (isset($validated['telephone'])) {
                $userUpdate['telephone'] = $validated['telephone'];
            }

            // Mettre à jour le mot de passe
            if (isset($validated['password']) && !empty($validated['password'])) {
                $userUpdate['password_hash'] = Hash::make($validated['password']);
            }

            // Appliquer les modifications utilisateur
            if (!empty($userUpdate)) {
                $user->update($userUpdate);
            }

            // ========== MISE À JOUR HÔPITAL ==========

            $hopitalUpdate = [
                'sync_statut' => 'pending', // Marquer pour synchronisation
            ];

            // Mettre à jour le nom
            if (isset($validated['nom'])) {
                $hopitalUpdate['nom'] = $validated['nom'];
            }

            // Mettre à jour la ville
            if (isset($validated['ville'])) {
                $hopitalUpdate['ville'] = $validated['ville'];
            }

            // Mettre à jour l'adresse
            if (isset($validated['adresse'])) {
                $hopitalUpdate['adresse'] = $validated['adresse'];
            }

            // Mettre à jour l'email
            if (isset($validated['email'])) {
                $hopitalUpdate['email'] = $validated['email'];
            }

            // Mettre à jour le téléphone principal
            if (isset($validated['telephone_principal'])) {
                $hopitalUpdate['telephone_principal'] = $validated['telephone_principal'];
            }

            // Gérer l'upload du logo
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
                $hopitalUpdate['logo'] = $logoPath;
            }

            // Appliquer les modifications hôpital
            if (!empty($hopitalUpdate)) {
                $hopital->update($hopitalUpdate);
            }

            DB::commit();

            // Recharger les données
            $user->refresh();
            $hopital->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => [
                    'id' => $user->id,
                    'login' => $user->login,
                    'telephone' => $user->telephone,
                    'role' => $user->role,
                ],
                'hopital' => [
                    'id' => $hopital->id,
                    'nom' => $hopital->nom,
                    'code_hopital' => $hopital->code_hopital,
                    'ville' => $hopital->ville,
                    'adresse' => $hopital->adresse,
                    'email' => $hopital->email,
                    'telephone_principal' => $hopital->telephone_principal,
                    'logo' => $hopital->logo,
                    'statut' => $hopital->statut,
                    'license_expires_at' => $hopital->license_expires_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔒 CHANGE PASSWORD : Changer le mot de passe (avec ancien mot de passe)
     * POST /api/hopitals/change-password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        $user = $request->user();

        // Vérifier l'ancien mot de passe
        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect',
            ], 401);
        }

        // Mettre à jour avec le nouveau mot de passe
        $user->update([
            'password_hash' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe changé avec succès',
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