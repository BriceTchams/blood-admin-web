<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamenPreDon extends Model
{
    use HasUuids;

    protected $fillable = [
        'donneur_id',
        'date_examen',
        'poids',
        'taille',
        'tension_arterielle',
        'pouls',
        'hemoglobine',
        'groupe_sanguin_rh',
        'hbv5',
        'hcv',
        'hiv',
        'tpha',
        'tdr_palu',
        'avis_responsable',
        'autorise',
        'date_prochaine_visite',
        'remarques',
        'sync_statut',
        'synced_at',
    ];

    protected $casts = [
        'date_examen' => 'datetime',
        'date_prochaine_visite' => 'date',
        'hbv5' => 'boolean',
        'hcv' => 'boolean',
        'hiv' => 'boolean',
        'tpha' => 'boolean',
        'tdr_palu' => 'boolean',
        'autorise' => 'boolean',
    ];

    // Relations
    public function donneur(): BelongsTo
    {
        return $this->belongsTo(Donneur::class, 'donneur_id');
    }

    public function ficheDon(): HasOne
    {
        return $this->hasOne(FicheDon::class, 'examen_pre_don_id');
    }

    // Métiers
    public function calculerImc(): float
    {
        if ($this->taille && $this->poids) {
            $taille_m = $this->taille / 100;
            return round($this->poids / ($taille_m ** 2), 2);
        }
        return 0;
    }

    public function validerCriteres(): bool
    {
        // IMC entre 18 et 33
        $imc = $this->calculerImc();
        if ($imc < 18 || $imc > 33) return false;

        // Poids >= 50 kg
        if ($this->poids < 50) return false;

        // Hémoglobine : femmes >= 12.5, hommes >= 13.5
        // (On simplifie ici, à adapter selon le sexe du donneur)
        if ($this->hemoglobine < 12.5) return false;

        // Pas de sérologie positive
        if ($this->hiv || $this->hbv5 || $this->hcv || $this->tpha) return false;

        return true;
    }

    public function genererDecision(): string
    {
        if ($this->validerCriteres()) {
            $this->update(['autorise' => true, 'date_prochaine_visite' => now()->addDays(56)]);
            return 'AUTORISE';
        }
        $this->update(['autorise' => false]);
        return 'REFUSE';
    }
}