<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Souscription;
use App\Models\Hopital;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SouscriptionController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
        // $this->middleware('auth:sanctum');
    }

    /**
     * Créer une souscription
     * POST /api/souscriptions
     */
  /**
 * Créer une souscription
 * POST /api/souscriptions
 */
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'hopital_id' => 'required|uuid|exists:hopitals,id',
        'plan' => 'required|in:trial,basic,premium,enterprise',
        'billing_period' => 'required|in:monthly,yearly',
        'notes' => 'nullable|string',
    ]);

    // Vérifier s'il existe déjà une souscription active
    $existing = Souscription::where('hopital_id', $validated['hopital_id'])
        ->where('statut', 'active')
        ->first();

    if ($existing) {
        return response()->json([
            'success' => false,
            'message' => 'Une souscription active existe déjà',
        ], 400);
    }

    try {
        // Déterminer la durée
        $months = $validated['billing_period'] === 'monthly' ? 1 : 12;

        // Créer la souscription
        $souscription = Souscription::create([
            'hopital_id' => $validated['hopital_id'],
            'plan' => $validated['plan'],
            'billing_period' => $validated['billing_period'],
            'date_souscription' => now(),
            'date_expiration' => now()->addMonths($months),
            'statut' => 'active',
            'notes' => $validated['notes'],
        ]);

        // \Log::info('✅ Souscription créée : ' . $souscription->id);

        // Générer automatiquement une licence
        $hopital = Hopital::find($validated['hopital_id']);
        
        // \Log::info('Avant création licence - Hôpital : ' . $hopital->id);

        $license = $this->licenseService->create(
            $hopital,
            $validated['plan'],
            $months,
            $this->getMaxUsersForPlan($validated['plan'])
        );

        // \Log::info('✅ License créée : ' . $license->license_key);

        return response()->json([
            'success' => true,
            'message' => 'Souscription créée',
            'data' => [
                'souscription' => [
                    'id' => $souscription->id,
                    'hopital_id' => $souscription->hopital_id,
                    'plan' => $souscription->plan,
                    'billing_period' => $souscription->billing_period,
                    'statut' => $souscription->statut,
                    'date_souscription' => $souscription->date_souscription,
                    'date_expiration' => $souscription->date_expiration,
                ],
                'license' => [
                    'license_key' => $license->license_key,
                    'expires_at' => $license->expires_at,
                ],
            ],
        ], 201);

    } catch (\Exception $e) {
        // \Log::error('❌ Erreur création souscription : ' . $e->getMessage());
        // \Log::error($e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Voir une souscription
     * GET /api/souscriptions/{id}
     */
    public function show(string $id): JsonResponse
    {
        $souscription = Souscription::with('hopital')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $souscription->id,
                'hopital' => $souscription->hopital->nom,
                'plan' => $souscription->plan,
                'billing_period' => $souscription->billing_period,
                'statut' => $souscription->statut,
                'is_active' => $souscription->isActive(),
                'is_expired' => $souscription->isExpired(),
                'date_souscription' => $souscription->date_souscription,
                'date_expiration' => $souscription->date_expiration,
                'days_remaining' => $souscription->daysRemaining(),
                'notes' => $souscription->notes,
            ],
        ]);
    }

    /**
     * Mettre à jour une souscription
     * PUT /api/souscriptions/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $souscription = Souscription::findOrFail($id);

        $validated = $request->validate([
            'plan' => 'nullable|in:trial,basic,premium,enterprise',
            'billing_period' => 'nullable|in:monthly,yearly',
            'statut' => 'nullable|in:active,inactive,suspended,cancelled',
            'notes' => 'nullable|string',
        ]);

        $souscription->update(array_filter($validated));

        return response()->json([
            'success' => true,
            'message' => 'Souscription mise à jour',
            'data' => $souscription,
        ]);
    }

    /**
     * Renouveler une souscription
     * POST /api/souscriptions/{id}/renew
     */
    public function renew(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:24',
        ]);

        $souscription = Souscription::findOrFail($id);
        $souscription->renew($validated['months']);

        // Renouveler aussi la licence
        $license = License::where('hopital_id', $souscription->hopital_id)->first();
        if ($license) {
            $this->licenseService->renew($license, $validated['months']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Souscription renouvelée',
            'data' => [
                'date_expiration' => $souscription->date_expiration,
                'days_remaining' => $souscription->daysRemaining(),
            ],
        ]);
    }

    /**
     * Suspendre une souscription
     * POST /api/souscriptions/{id}/suspend
     */
    public function suspend(string $id): JsonResponse
    {
        $souscription = Souscription::findOrFail($id);
        $souscription->suspend();

        // Révoquer la licence aussi
        $license = License::where('hopital_id', $souscription->hopital_id)->first();
        if ($license) {
            $license->revoke();
        }

        return response()->json([
            'success' => true,
            'message' => 'Souscription suspendue',
        ]);
    }

    /**
     * Annuler une souscription
     * POST /api/souscriptions/{id}/cancel
     */
    public function cancel(string $id): JsonResponse
    {
        $souscription = Souscription::findOrFail($id);
        $souscription->cancel();

        // Révoquer la licence aussi
        $license = License::where('hopital_id', $souscription->hopital_id)->first();
        if ($license) {
            $license->revoke();
        }

        return response()->json([
            'success' => true,
            'message' => 'Souscription annulée',
        ]);
    }

    /**
     * Lister les souscriptions d'un hôpital
     * GET /api/hopitals/{hopital_id}/souscriptions
     */
    public function byHopital(string $hopital_id): JsonResponse
    {
        $hopital = Hopital::findOrFail($hopital_id);
        
        $souscriptions = Souscription::where('hopital_id', $hopital_id)
            ->with('hopital')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'plan' => $s->plan,
                'billing_period' => $s->billing_period,
                'statut' => $s->statut,
                'is_active' => $s->isActive(),
                'date_expiration' => $s->date_expiration,
                'days_remaining' => $s->daysRemaining(),
            ]);

        return response()->json([
            'success' => true,
            'hopital' => $hopital->nom,
            'souscriptions' => $souscriptions,
        ]);
    }

    /**
     * Tableau de bord des souscriptions
     * GET /api/admin/souscriptions
     */
    public function dashboard(): JsonResponse
    {
        $souscriptions = Souscription::with('hopital')->get();

        $stats = [
            'total' => $souscriptions->count(),
            'active' => $souscriptions->where('statut', 'active')->count(),
            'suspended' => $souscriptions->where('statut', 'suspended')->count(),
            'cancelled' => $souscriptions->where('statut', 'cancelled')->count(),
            'expiring_soon' => $souscriptions->filter(fn($s) => $s->daysRemaining() <= 30 && $s->isActive())->count(),
        ];

        $list = $souscriptions->map(fn($s) => [
            'id' => $s->id,
            'hopital' => $s->hopital->nom,
            'plan' => $s->plan,
            'statut' => $s->statut,
            'date_expiration' => $s->date_expiration,
            'days_remaining' => $s->daysRemaining(),
        ]);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'souscriptions' => $list,
        ]);
    }

    /**
     * Déterminer le nombre d'utilisateurs selon le plan
     */
    private function getMaxUsersForPlan(string $plan): int
    {
        return match($plan) {
            'trial' => 3,
            'basic' => 5,
            'premium' => 15,
            'enterprise' => 100,
            default => 5,
        };
    }
} 