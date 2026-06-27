<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Souscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'hopital_id',
        'plan',
        'billing_period',
        'date_souscription',
        'date_expiration',
        'date_renouvellement',
        'statut',
        'notes',
        'sync_statut',
        'synced_at',
    ];

    protected $casts = [
        'date_souscription' => 'datetime',
        'date_expiration' => 'datetime',
        'date_renouvellement' => 'datetime',
        'synced_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================

    /**
     * Relation vers l'hôpital
     */
    public function hopital(): BelongsTo
    {
        return $this->belongsTo(Hopital::class, 'hopital_id');
    }

    /**
     * Relation vers la licence associée
     */
    public function license(): HasOne
    {
        return $this->hasOne(License::class, 'souscription_id');
    }

    // ============================================================
    // MÉTHODES EN FRANÇAIS (originalité du projet)
    // ============================================================

    /**
     * Vérifier si la souscription est expirée
     */
    public function estExpire(): bool
    {
        return now()->gt($this->date_expiration);
    }

    /**
     * Vérifier si la souscription est active
     */
    public function estActive(): bool
    {
        return $this->statut === 'active' && !$this->estExpire();
    }

    /**
     * Obtenir le nombre de jours jusqu'à l'expiration
     */
    public function jursJusquaExpiration(): int
    {
        return max(0, now()->diffInDays($this->date_expiration));
    }

    /**
     * Activer la souscription
     */
    public function souscrire(): void
    {
        $this->date_souscription = now();
        $this->statut = 'active';
        
        // Déterminer la date d'expiration selon la période de facturation
        if ($this->billing_period === 'monthly') {
            $this->date_expiration = now()->addMonth();
        } else {
            $this->date_expiration = now()->addYear();
        }
        
        $this->save();
    }

    /**
     * Renouveler la souscription
     */
    public function renouveler(): void
    {
        // Renouveler pour une période supplémentaire
        if ($this->billing_period === 'monthly') {
            $this->date_expiration = $this->date_expiration->addMonth();
        } else {
            $this->date_expiration = $this->date_expiration->addYear();
        }
        
        $this->date_renouvellement = now();
        $this->statut = 'active';
        $this->save();
    }

    /**
     * Résilier (annuler) la souscription
     */
    public function resilier(): void
    {
        $this->statut = 'cancelled';
        $this->save();
    }

    /**
     * Suspendre la souscription
     */
    public function suspendre(): void
    {
        $this->statut = 'suspended';
        $this->save();
    }

    /**
     * Vérifier la validité de la souscription
     */
    public function verifierValidite(): bool
    {
        return $this->estActive();
    }

    // ============================================================
    // ALIAS EN ANGLAIS (pour compatibilité contrôleur API)
    // ============================================================

    /**
     * Alias anglais : isActive()
     */
    public function isActive(): bool
    {
        return $this->estActive();
    }

    /**
     * Alias anglais : isExpired()
     */
    public function isExpired(): bool
    {
        return $this->estExpire();
    }

    /**
     * Alias anglais : daysRemaining()
     */
    public function daysRemaining(): int
    {
        return $this->jursJusquaExpiration();
    }

    /**
     * Alias anglais : renew()
     * Renouveler pour un nombre de mois supplémentaires
     */
    public function renew(int $months): void
    {
        $this->update([
            'date_expiration' => $this->date_expiration->addMonths($months),
            'date_renouvellement' => now(),
            'statut' => 'active',
        ]);
    }

    /**
     * Alias anglais : cancel()
     */
    public function cancel(): void
    {
        $this->resilier();
    }

    /**
     * Alias anglais : suspend()
     */
    public function suspend(): void
    {
        $this->suspendre();
    }

    // ============================================================
    // INFORMATIONS SELON LE PLAN
    // ============================================================

    /**
     * Obtenir les détails d'un plan
     * 
     * @param string $plan Le type de plan
     * @return array Détails du plan
     */
    public static function getPlanDetails(string $plan): array
    {
        $plans = [
            'trial' => [
                'nom' => 'Essai Gratuit',
                'max_users' => 3,
                'duree_jours' => 30,
                'prix_mensuel' => 0,
                'prix_annuel' => 0,
                'features' => ['donneurs', 'dons_basiques', 'rapports_simples'],
                'description' => 'Plan d\'essai limité à 30 jours',
            ],
            'basic' => [
                'nom' => 'Basic',
                'max_users' => 5,
                'duree_jours' => null,
                'prix_mensuel' => 50000,
                'prix_annuel' => 500000,
                'features' => ['donneurs', 'dons_complets', 'stock_sanguin', 'rapports'],
                'description' => 'Plan basique pour petites structures',
                'devise' => 'FCFA',
            ],
            'premium' => [
                'nom' => 'Premium',
                'max_users' => 15,
                'duree_jours' => null,
                'prix_mensuel' => 150000,
                'prix_annuel' => 1500000,
                'features' => ['donneurs', 'dons_complets', 'stock_sanguin', 'rapports_avances', 'sms_notifications', 'api_access'],
                'description' => 'Plan complet pour structures moyennes',
                'devise' => 'FCFA',
            ],
            'enterprise' => [
                'nom' => 'Entreprise',
                'max_users' => null,
                'duree_jours' => null,
                'prix_mensuel' => 500000,
                'prix_annuel' => 5000000,
                'features' => ['all', 'support_prioritaire', 'formation', 'integrations_custom'],
                'description' => 'Plan sur mesure pour grandes structures',
                'devise' => 'FCFA',
            ],
        ];

        return $plans[$plan] ?? $plans['trial'];
    }

    /**
     * Obtenir le nombre maximum d'utilisateurs selon le plan
     */
    public function getMaxUsersForPlan(): int
    {
        $details = self::getPlanDetails($this->plan);
        return $details['max_users'] ?? 5;
    }

    /**
     * Obtenir les features du plan courant
     */
    public function getFeatures(): array
    {
        $details = self::getPlanDetails($this->plan);
        return $details['features'] ?? [];
    }

    /**
     * Obtenir le nom du plan
     */
    public function getPlanName(): string
    {
        $details = self::getPlanDetails($this->plan);
        return $details['nom'] ?? 'Inconnu';
    }
}