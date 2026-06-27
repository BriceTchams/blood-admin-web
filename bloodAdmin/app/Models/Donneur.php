<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * Donneur
 *
 * Méthodes du diagramme :
 *   + inscrire() : void
 *   + modifierInfos() : void
 *   + voirHistorique() : List
 *   + getAge() : Int
 */
class Donneur extends Model
{
    use HasUuids;

    public const STATUT_ACTIF = 'actif';
    public const STATUT_INACTIF = 'inactif';
    public const STATUT_SUSPENDU = 'suspendu';
    public const STATUT_INELIGIBLE = 'ineligible';

    /** Âge minimum légal pour donner du sang */
    public const AGE_MIN = 18;
    /** Âge maximum généralement retenu pour un premier don / don régulier */
    public const AGE_MAX = 65;
    /** Poids minimum (kg) couramment exigé pour pouvoir donner */
    public const POIDS_MIN = 50;

    protected $table = 'donneurs';

    protected $fillable = [
        'hopital_id',
        'groupe_sanguin_id',
        'nom',
        'prenom',
        'date_naissance',
        'poids',
        'telephone',
        'email',
        'statut',
        'deleted',
        'sync_statut',
        'uuid',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'poids' => 'float',
        'deleted' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function groupeSanguin(): BelongsTo
    {
        return $this->belongsTo(GroupeSanguin::class, 'groupe_sanguin_id');
    }

    /**
     * NB : le module Hôpital n'est pas encore implémenté dans ce lot. La relation est
     * préparée (cf. diagramme « Hopital 1 ──► 0..* Donneur : enregistre ») et pourra être
     * activée dès que le modèle App\Models\Hopital existera.
     */
    public function hopital(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Hopital::class, 'hopital_id');
    }

    /**
     * Historique des dons (cf. diagramme « Donneur 1 ──► 0..* FicheDon »).
     * Préparé pour le module "Parcours de don", pas encore livré dans ce lot.
     */
    // public function fichesDon(): HasMany
    // {
    //     return $this->hasMany(\App\Models\FicheDon::class, 'donneur_id');
    // }

    /**
     * Examens pré-don (cf. diagramme « Donneur 1 ──► 0..* ExamenPreDon »).
     */
    // public function examensPreDon(): HasMany
    // {
    //     return $this->hasMany(\App\Models\ExamenPreDon::class, 'donneur_id');
    // }

    /**
     * Notifications reçues (cf. diagramme « Donneur 1 ──► 0..* Notification »).
     */
    // public function notifications(): HasMany
    // {
    //     return $this->hasMany(\App\Models\Notification::class, 'donneur_id');
    // }

    /*
    |--------------------------------------------------------------------------
    | Méthodes métier
    |--------------------------------------------------------------------------
    */

    /**
     * + getAge() : Int
     * Âge en années révolues, calculé à partir de date_naissance.
     */
    public function getAge(): int
    {
        if (! $this->date_naissance) {
            return 0;
        }

        return Carbon::parse($this->date_naissance)->age;
    }

    /**
     * + inscrire() : void
     *
     * Enregistre un nouveau donneur en appliquant les règles d'éligibilité de base
     * (âge, poids). Lève une ValidationException si le donneur n'est pas éligible.
     * (Les critères médicaux fins restent du ressort de ExamenPreDon::validerCriteres().)
     */
    public function inscrire(): void
    {
        $erreurs = [];

        $age = $this->getAge();
        if ($age < self::AGE_MIN) {
            $erreurs[] = "Le donneur doit avoir au moins ".self::AGE_MIN." ans (âge actuel : {$age}).";
        }
        if ($age > self::AGE_MAX) {
            $erreurs[] = "Le donneur ne doit pas dépasser ".self::AGE_MAX." ans (âge actuel : {$age}).";
        }
        if ($this->poids !== null && $this->poids < self::POIDS_MIN) {
            $erreurs[] = "Le poids minimum requis pour donner est de ".self::POIDS_MIN." kg.";
        }

        if (! empty($erreurs)) {
            throw ValidationException::withMessages(['donneur' => $erreurs]);
        }

        $this->statut = $this->statut ?: self::STATUT_ACTIF;
        $this->sync_statut = 'pending';
        $this->save();
    }

    /**
     * + modifierInfos() : void
     * Met à jour les informations du donneur et marque l'enregistrement comme
     * "à synchroniser" (cf. stratégie offline-first, guide §3.2).
     */
    public function modifierInfos(array $donnees): void
    {
        $this->fill(\Illuminate\Support\Arr::only($donnees, [
            'nom', 'prenom', 'date_naissance', 'poids', 'telephone', 'email',
            'statut', 'groupe_sanguin_id',
        ]));

        $this->sync_statut = 'pending';
        $this->save();
    }

    /**
     * + voirHistorique() : List
     * Historique chronologique des dons effectués (FicheDon), du plus récent au plus ancien.
     * Retourne une collection vide tant que le module "Parcours de don" n'est pas livré /
     * que la table fiche_dons n'existe pas encore.
     */
    public function voirHistorique()
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('fiche_dons')) {
            return collect();
        }

        return $this->fichesDon()->orderByDesc('date_don')->get();
    }

    /**
     * Nombre de jours restants avant la prochaine date de don autorisée, basé sur la
     * dernière FicheDon (cf. FicheDon::calculerDateProchainDon()). Retourne null si
     * l'information n'est pas disponible (aucun don encore enregistré / module absent).
     */
    public function joursAvantProchainDonPossible(): ?int
    {
        $derniereFiche = $this->voirHistorique()->first();

        if (! $derniereFiche || ! $derniereFiche->date_prochain_don) {
            return null;
        }

        return now()->diffInDays(Carbon::parse($derniereFiche->date_prochain_don), false);
    }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActifs($query)
    {
        return $query->where('statut', self::STATUT_ACTIF)->where('deleted', false);
    }

    public function scopeDuGroupe($query, string $libelleComplet)
    {
        return $query->whereHas('groupeSanguin', function ($q) use ($libelleComplet) {
            $q->where('libelle', substr($libelleComplet, 0, -1))
                ->where('rhesus', substr($libelleComplet, -1));
        });
    }
}
