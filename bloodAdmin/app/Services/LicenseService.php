<?php

namespace App\Services;

use App\Models\License;
use App\Models\Hopital;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LicenseService
{
    /**
     * Générer une clé aléatoire
     */
    public function generateKey(): string
    {
        // Format : LICENSE-XXXXXXXX-XXXXXXXX
        return 'LICENSE-' . Str::upper(Str::random(16)) . '-' . Str::upper(Str::random(16));
    }

    /**
     * Créer une licence pour un hôpital
     */
            public function create(
                Hopital $hopital,
                string $plan,
                int $months,
                int $maxUsers = 5
            ): License {

                // Vérifier s'il existe déjà
                $existing = License::firstWhere('hopital_id', $hopital->id);

                if ($existing) {
                    $existing->revoke();
                }

                // Créer la nouvelle
                return License::create([
                    'hopital_id' => $hopital->id,
                    'license_key' => $this->generateKey(),
                    'plan' => $plan,
                    'max_users' => $maxUsers,
                    'created_at' => now(),
                    'expires_at' => now()->addMonths($months),
                    'status' => 'active',
                ]);
            }

    /**
     * Renouveler une licence
     */
    public function renew(License $license, int $months): License
    {
        $license->update([
            'expires_at' => $license->expires_at->addMonths($months),
            'status' => 'active',
        ]);

        return $license;
    }

    /**
     * Vérifier une licence
     */
        public function verify(string $licenseKey): ?License
        {
            return License::query()
                ->firstWhere('license_key', $licenseKey);
        }
}