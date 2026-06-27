<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenPostDon extends Model
{
    use HasUuids;

    protected $fillable = [
        'fiche_don_id',
        'donneur_id',
        'date_examen',
        'do_tpha',
        'vs_tpha',
        'do_hiv',
        'vs_hiv',
        'do_hbsag',
        'vs_hbsag',
        'do_hcv',
        'vs_hcv',
        'interpretation_elisa',
        'groupe_sanguin',
        'rhesus',
        'coombs_direct',
        'coombs_indirect',
        'electrophorese_hb',
        'vdrl',
        'statut_final',
        'sync_statut',
        'synced_at',
    ];

    protected $casts = [
        'date_examen' => 'datetime',
        'do_tpha' => 'float',
        'vs_tpha' => 'float',
        'do_hiv' => 'float',
        'vs_hiv' => 'float',
        'do_hbsag' => 'float',
        'vs_hbsag' => 'float',
        'do_hcv' => 'float',
        'vs_hcv' => 'float',
        'coombs_direct' => 'boolean',
        'coombs_indirect' => 'boolean',
        'vdrl' => 'boolean',
    ];

    // Relations
    public function ficheDon(): BelongsTo
    {
        return $this->belongsTo(FicheDon::class, 'fiche_don_id');
    }

    public function donneur(): BelongsTo
    {
        return $this->belongsTo(Donneur::class, 'donneur_id');
    }

    // Métiers
    public function interpreterElisa(): string
    {
        $resultats = [];

        // TPHA
        if ($this->do_tpha >= $this->vs_tpha) {
            $resultats[] = 'TPHA+';
        }

        // HIV
        if ($this->do_hiv >= $this->vs_hiv) {
            $resultats[] = 'HIV+';
        }

        // HBsAg
        if ($this->do_hbsag >= $this->vs_hbsag) {
            $resultats[] = 'HBsAg+';
        }

        // HCV
        if ($this->do_hcv >= $this->vs_hcv) {
            $resultats[] = 'HCV+';
        }

        $interpretation = empty($resultats) ? 'NEGATIF' : implode(' | ', $resultats);
        $this->update(['interpretation_elisa' => $interpretation]);

        return $interpretation;
    }

    public function validerSang(): bool
    {
        // ELISA négatif
        if (str_contains($this->interpretation_elisa, '+')) {
            return false;
        }

        // Pas de sérologie positive
        if ($this->vdrl || $this->coombs_direct) {
            return false;
        }

        return true;
    }

    public function genererResultats(): void
    {
        $this->interpreterElisa();

        if ($this->validerSang()) {
            $this->statut_final = 'conforme';
        } else {
            $this->statut_final = 'non_conforme';
        }

        $this->save();

        // Mettre à jour la fiche de don
        if ($this->statut_final === 'conforme') {
            $this->ficheDon()->update(['statut' => 'valide']);
            $this->ficheDon->calculerDateProchainDon();
        }
    }
}