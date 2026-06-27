<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Hopital;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LicenseController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
        // $this->middleware('auth:sanctum')->except(['verify']);
    }

    /**
     * Générer une licence
     * POST /api/licenses/generate
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hopital_id' => 'required|uuid|exists:hopitals,id',
            'plan' => 'required|in:trial,basic,premium,enterprise',
            'months' => 'required|integer|min:1|max:24',
            'max_users' => 'nullable|integer|min:1',
        ]);

        $hopital = Hopital::findOrFail($validated['hopital_id']);        
        $license = $this->licenseService->create(
            $hopital,
            $validated['plan'],
            $validated['months'],
            $validated['max_users'] ?? 5
        );

        return response()->json([
            'success' => true,
            'message' => 'Licence générée',
            'data' => [
                'id' => $license->id,
                'license_key' => $license->license_key,
                'hopital' => $hopital->nom,
                'plan' => $license->plan,
                'expires_at' => $license->expires_at->toDateString(),
            ],
        ], 201);
    }

    /**
     * Renouveler une licence
     * POST /api/licenses/{id}/renew
     */
    public function renew(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:24',
        ]);

        $license = License::findOrFail($id);
        $this->licenseService->renew($license, $validated['months']);

        return response()->json([
            'success' => true,
            'message' => 'Licence renouvelée',
            'data' => [
                'license_key' => $license->license_key,
                'expires_at' => $license->expires_at->toDateString(),
            ],
        ]);
    }

    /**
     * Révoquer une licence
     * POST /api/licenses/{id}/revoke
     */
    public function revoke(string $id): JsonResponse
    {
        $license = License::findOrFail($id);
        $license->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Licence révoquée',
        ]);
    }

    /**
     * Vérifier une licence (PUBLIC - pas de auth)
     * GET /api/licenses/verify?key=LICENSE-XXXX
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string',
        ]);

        $license = $this->licenseService->verify($validated['key']);

        if (!$license) {
            return response()->json([
                'valid' => false,
                'message' => 'Licence non trouvée',
            ], 404);
        }

        return response()->json([
            'valid' => $license->isValid(),
            'status' => $license->status,
            'plan' => $license->plan,
            'max_users' => $license->max_users,
            'expires_at' => $license->expires_at->toDateString(),
            'days_remaining' => $license->daysRemaining(),
        ]);
    }

    /**
     * Lister les licences d'un hôpital
     * GET /api/hopitals/{id}/licenses
     */
    public function listByHopital(string $hopital_id): JsonResponse
    {
        $hopital = Hopital::findOrFail($hopital_id);
        
        $license = $hopital->license;

        if (!$license) {
            return response()->json([
                'hopital' => $hopital->nom,
                'license' => null,
            ]);
        }

        return response()->json([
            'hopital' => $hopital->nom,
            'license' => [
                'id' => $license->id,
                'license_key' => $license->license_key,
                'plan' => $license->plan,
                'status' => $license->status,
                'is_valid' => $license->isValid(),
                'created_at' => $license->created_at->toDateString(),
                'expires_at' => $license->expires_at->toDateString(),
                'days_remaining' => $license->daysRemaining(),
            ],
        ]);
    }

    /**
     * Tableau de bord (Admin)
     * GET /api/admin/licenses
     */
    public function dashboard(): JsonResponse
    {
        $licenses = License::with('hopital')->get();

        $stats = [
            'total' => $licenses->count(),
            'active' => $licenses->where('status', 'active')->count(),
            'expired' => $licenses->where('status', 'expired')->count(),
            'revoked' => $licenses->where('status', 'revoked')->count(),
            'expiring_soon' => $licenses->filter(fn($l) => $l->daysRemaining() <= 30 && $l->isValid())->count(),
        ];

        $list = $licenses->map(fn($l) => [
            'id' => $l->id,
            'hopital' => $l->hopital->nom,
            'license_key' => $l->license_key,
            'plan' => $l->plan,
            'status' => $l->status,
            'expires_at' => $l->expires_at->toDateString(),
            'days_remaining' => $l->daysRemaining(),
        ]);

        return response()->json([
            'stats' => $stats,
            'licenses' => $list,
        ]);
    }
}