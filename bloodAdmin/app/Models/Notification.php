<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification
 *
 * - id : UUID
 * - donneur_id : UUID   (Donneur 1 ──► 0..* Notification : reçoit)
 * - hopital_id : UUID   (Hopital 1 ──► 0..* Notification : envoie)
 * - message : String
 * - dateEnvoi : Date
 * - statut : String   // lu | non_lu
 *
 * Méthodes du diagramme :
 *   + envoyer() : void
 *   + marquerLue() : void
 *   + supprimerNotif() : void
 */
class Notification extends Model
{
    use HasUuids;

    public const STATUT_LUE = 'lu';
    public const STATUT_NON_LUE = 'non_lu';

    protected $table = 'notifications';

    protected $fillable = [
        'donneur_id',
        'hopital_id',
        'message',
        'date_envoi',
        'statut',
        'deleted',
        'sync_statut',
        'uuid',
    ];

    protected $casts = [
        'date_envoi' => 'datetime',
        'deleted' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function donneur(): BelongsTo
    {
        return $this->belongsTo(Donneur::class, 'donneur_id');
    }

    /**
     * NB : module Hôpital pas encore livré dans ce lot — relation préparée pour
     * App\Models\Hopital (cf. diagramme « Hopital 1 ──► 0..* Notification : envoie »).
     */
    public function hopital(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Hopital::class, 'hopital_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Méthodes métier
    |--------------------------------------------------------------------------
    */

    /**
     * + envoyer() : void
     *
     * Marque la notification comme envoyée (horodatage `date_envoi`) et la persiste.
     * Conformément au guide §2.3 (planification J-3), l'envoi réel d'un SMS est
     * différé/exécuté par la file `sms_queue` dès qu'une connexion est détectée ;
     * cette méthode représente la "création / déclenchement logique" de la
     * notification côté app Hôpital, pas la livraison réseau elle-même.
     */
    public function envoyer(): void
    {
        $this->date_envoi = $this->date_envoi ?: now();
        $this->statut = $this->statut ?: self::STATUT_NON_LUE;
        $this->sync_statut = 'pending';
        $this->save();
    }

    /**
     * + marquerLue() : void
     * Le donneur (ou l'agent hospitalier consultant en son nom) a pris connaissance
     * de la notification.
     */
    public function marquerLue(): void
    {
        $this->statut = self::STATUT_LUE;
        $this->sync_statut = 'pending';
        $this->save();
    }

    /**
     * + supprimerNotif() : void
     * Suppression logique (cohérente avec la stratégie offline-first : on ne supprime
     * jamais réellement une ligne déjà synchronisée, on la marque `deleted`).
     */
    public function supprimerNotif(): void
    {
        $this->deleted = true;
        $this->sync_statut = 'pending';
        $this->save();
    }

    /**
     * Raccourci métier : crée (sans l'envoyer) une notification de rappel J-3 pour un
     * donneur, à partir de sa prochaine date de don prévue. Usage typique :
     *
     *   Notification::creerRappelDon($donneur, $dateProchainDon)->envoyer();
     */
    public static function creerRappelDon(Donneur $donneur, \DateTimeInterface $dateProchainDon): self
    {
        return new self([
            'donneur_id' => $donneur->id,
            'hopital_id' => $donneur->hopital_id,
            'message' => sprintf(
                'Bonjour %s, votre prochain don de sang est prévu le %s. Merci de votre engagement !',
                $donneur->prenom,
                \Illuminate\Support\Carbon::parse($dateProchainDon)->format('d/m/Y')
            ),
            'statut' => self::STATUT_NON_LUE,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeNonLues($query)
    {
        return $query->where('statut', self::STATUT_NON_LUE)->where('deleted', false);
    }

    public function scopeDuDonneur($query, string $donneurId)
    {
        return $query->where('donneur_id', $donneurId);
    }

    public function scopeVisibles($query)
    {
        return $query->where('deleted', false);
    }
}
