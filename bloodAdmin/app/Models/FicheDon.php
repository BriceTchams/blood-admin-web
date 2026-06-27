<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FicheDon extends Model
{
    use HasUuids;

    protected $fillable = [
        'donneur_id',
        'hopital_id',
        'examen_pre_don_id',
        'numero_don',
        'type_donneur',
        'date_don',
        'date_prochain_don',
        'nombre_poches',
        'volume_preleve_ml',
        'statut',
        'deleted',
        'sync_statut',
        'synced_at',
    ];

    protected $casts = [
        'date_don' => 'datetime',
        'date_prochain_don' => 'date',
        'deleted' => 'boolean',
    ];

    // Relations
    public function donneur(): BelongsTo
    {
        return $this->belongsTo(Donneur::class, 'donneur_id');
    }

    public function hopital(): BelongsTo
    {
        return $this->belongsTo(Hopital::class, 'hopital_id');
    }

    public function examenPreDon(): BelongsTo
    {
        return $this->belongsTo(ExamenPreDon::class, 'examen_pre_don_id');
    }

    public function examenPostDon(): HasOne
    {
        return $this->hasOne(ExamenPostDon::class, 'fiche_don_id');
    }

    // Métiers
    public static function genererNumero(string $hopitalId): string
    {
        // Format : HOPITAL_YYYY_MMDD_XXXXXX (6 chiffres aléatoires)
        $hopital = Hopital::find($hopitalId);
        $abbrev = Str::upper(Str::limit($hopital->nom, 3, ''));
        $date = now()->format('YmdHi');
        $random = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        return "{$abbrev}_{$date}_{$random}";
    }

    public function creer(): void
    {
        $this->numero_don = self::genererNumero($this->hopital_id);
        $this->date_don = now();
        $this->statut = 'en_cours';
        $this->save();
    }

    public function valider(): void
    {
        if ($this->examenPostDon && $this->examenPostDon->statut_final === 'conforme') {
            $this->statut = 'valide';
            $this->calculerDateProchainDon();
            $this->save();
        }
    }

    public function annuler(): void
    {
        $this->statut = 'annule';
        $this->save();
    }

    public function calculerDateProchainDon(): void
    {
        // 56 jours (8 semaines) après la date du don
        $this->date_prochain_don = $this->date_don->addDays(56)->toDateString();
        $this->save();
    }
}