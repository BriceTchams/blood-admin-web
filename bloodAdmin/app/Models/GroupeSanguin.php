<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * GroupeSanguin
 *
 * - id : UUID
 * - libelle : A | B | AB | O
 * - rhesus  : + | -
 *
 * Méthodes du diagramme :
 *   + getLibelleComplet() : String   -> "A+", "O-", ...
 *   + estCompatibleAvec(gs) : Boolean -> ce groupe (donneur) peut-il donner à $gs (receveur) ?
 */
class GroupeSanguin extends Model
{
    use HasUuids;

    public const LIBELLES = ['A', 'B', 'AB', 'O'];
    public const RHESUS = ['+', '-'];

    protected $table = 'groupe_sanguins';

    protected $fillable = [
        'libelle',
        'rhesus',
        'deleted',
        'sync_statut',
        'uuid',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function donneurs(): HasMany
    {
        return $this->hasMany(Donneur::class, 'groupe_sanguin_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Méthodes métier
    |--------------------------------------------------------------------------
    */

    /**
     * + getLibelleComplet() : String
     * Exemple : libelle = "AB", rhesus = "-" -> "AB-"
     */
    public function getLibelleComplet(): string
    {
        return $this->libelle.$this->rhesus;
    }

    /**
     * + estCompatibleAvec(gs) : Boolean
     *
     * Indique si CE groupe (donneur) peut donner du sang à $receveur (compatibilité
     * érythrocytaire classique, transfusion de sang total / culot globulaire).
     *
     * Règle ABO : O donne à tous, A donne à A/AB, B donne à B/AB, AB donne uniquement à AB.
     * Règle Rh  : un donneur Rh- peut donner à Rh- et Rh+, un donneur Rh+ ne peut donner qu'à Rh+.
     */
    public function estCompatibleAvec(GroupeSanguin $receveur): bool
    {
        $compatibiliteABO = [
            'O' => ['O', 'A', 'B', 'AB'],
            'A' => ['A', 'AB'],
            'B' => ['B', 'AB'],
            'AB' => ['AB'],
        ];

        $aboOk = in_array($receveur->libelle, $compatibiliteABO[$this->libelle] ?? [], true);

        $rhOk = $this->rhesus === '-' ? true : $receveur->rhesus === '+';

        return $aboOk && $rhOk;
    }

    /**
     * Liste tous les groupes (parmi ceux fournis ou tous les groupes en base) que ce groupe
     * peut recevoir comme don. Utile pour les écrans Filament ("groupes compatibles").
     */
    public function recoitDe(iterable $groupes = null): array
    {
        $groupes ??= self::all();

        return collect($groupes)
            ->filter(fn (GroupeSanguin $donneur) => $donneur->estCompatibleAvec($this))
            ->values()
            ->all();
    }

    // public static function findByLibelleComplet(string $libelleComplet): ?self
    // {
    //     $rhesus = substr($libelleComplet, -1);
    //     $libelle = substr($libelleComplet, 0, -1);

    //     return static::where('libelle', $libelle)->where('rhesus', $rhesus)->first();
    // }
}
