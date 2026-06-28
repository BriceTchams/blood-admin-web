<?php

namespace App\Services;

use App\Models\Hopital;
use App\Models\SyncLog;
use App\Models\SyncQueue;
use App\Models\Donneur;
use App\Models\ExamenPreDon;
use App\Models\FicheDon;
use App\Models\ExamenPostDon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncService
{
    protected Hopital $hopital;
    protected SyncLog $syncLog;

    public function __construct()
    {
    }

    /**
     * Commencer une synchronisation PUSH
     */
    public function startPushSync(Hopital $hopital): SyncLog
    {
        $this->hopital = $hopital;

        $this->syncLog = SyncLog::create([
            'hopital_id' => $hopital->id,
            'type' => 'push',
            'statut' => 'pending',
            'started_at' => now(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        return $this->syncLog;
    }

    /**
     * Commencer une synchronisation PULL
     */
    public function startPullSync(Hopital $hopital): SyncLog
    {
        $this->hopital = $hopital;

        $this->syncLog = SyncLog::create([
            'hopital_id' => $hopital->id,
            'type' => 'pull',
            'statut' => 'pending',
            'started_at' => now(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        return $this->syncLog;
    }

    /**
     * Récupérer les données à pusher
     */
    public function getPushData(): array
    {
        $lastSync = $this->hopital->last_synced_at ?? now()->subYear();

        return [
            'donneurs' => Donneur::where('hopital_id', $this->hopital->id)
                ->where('updated_at', '>', $lastSync)
                ->get()
                ->map(fn($d) => $this->serializeModel($d, 'Donneur')),

            'examens_pre_don' => ExamenPreDon::whereHas('donneur', fn($q) => 
                $q->where('hopital_id', $this->hopital->id)
            )
            ->where('updated_at', '>', $lastSync)
            ->get()
            ->map(fn($e) => $this->serializeModel($e, 'ExamenPreDon')),

            'fiches_don' => FicheDon::where('hopital_id', $this->hopital->id)
                ->where('updated_at', '>', $lastSync)
                ->get()
                ->map(fn($f) => $this->serializeModel($f, 'FicheDon')),

            'examens_post_don' => ExamenPostDon::whereHas('ficheDon', fn($q) => 
                $q->where('hopital_id', $this->hopital->id)
            )
            ->where('updated_at', '>', $lastSync)
            ->get()
            ->map(fn($e) => $this->serializeModel($e, 'ExamenPostDon')),
        ];
    }

    /**
     * Traiter les données reçues du PUSH
     */
    public function processPushData(array $data): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'conflicts' => 0,
        ];

        try {
            DB::beginTransaction();

            // Traiter chaque type de données
            foreach ($data['donneurs'] ?? [] as $donneur) {
                $stats = $this->upsertRecord(Donneur::class, $donneur, $stats);
            }

            foreach ($data['examens_pre_don'] ?? [] as $examen) {
                $stats = $this->upsertRecord(ExamenPreDon::class, $examen, $stats);
            }

            foreach ($data['fiches_don'] ?? [] as $fiche) {
                $stats = $this->upsertRecord(FicheDon::class, $fiche, $stats);
            }

            foreach ($data['examens_post_don'] ?? [] as $examen) {
                $stats = $this->upsertRecord(ExamenPostDon::class, $examen, $stats);
            }

            DB::commit();

            // Mettre à jour le timestamp de sync
            $this->hopital->update(['last_synced_at' => now()]);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Insérer ou mettre à jour un enregistrement
     */
    private function upsertRecord(string $modelClass, array $data, array $stats): array
    {
        $model = new $modelClass();
        
        // Vérifier si l'enregistrement existe
        $existing = $modelClass::where('id', $data['id'])->first();

        if ($existing) {
            // Déterminer la version la plus récente (last-write-wins)
            if (isset($data['updated_at']) && $existing->updated_at < $data['updated_at']) {
                $existing->update($data);
                $stats['updated']++;
            } else if ($existing->updated_at !== $data['updated_at']) {
                // Conflit détecté
                $stats['conflicts']++;
                SyncQueue::create([
                    'hopital_id' => $this->hopital->id,
                    'table_name' => class_basename($modelClass),
                    'record_id' => $data['id'],
                    'operation' => 'update',
                    'payload' => $data,
                    'statut' => 'conflict',
                ]);
            }
        } else {
            // Créer le nouvel enregistrement
            $modelClass::create($data);
            $stats['created']++;
        }

        return $stats;
    }

    /**
     * Sérialiser un modèle pour la transmission
     */
    private function serializeModel($model, string $type): array
    {
        return array_merge(
            $model->toArray(),
            [
                'model_type' => $type,
                'synced_at' => now(),
            ]
        );
    }

    /**
     * Récupérer les données globales pour le PULL
     */
    public function getPullData(): array
    {
        return [
            'hopital' => $this->hopital,
            'statistiques' => $this->getStatistiques(),
            'configuration' => $this->getConfiguration(),
            'timestamp' => now(),
        ];
    }

    /**
     * Statistiques globales
     */
    private function getStatistiques(): array
    {
        return [
            'total_donneurs' => Donneur::where('hopital_id', $this->hopital->id)->count(),
            'total_dons' => FicheDon::where('hopital_id', $this->hopital->id)->count(),
            'dons_ce_mois' => FicheDon::where('hopital_id', $this->hopital->id)
                ->whereYear('date_don', now()->year)
                ->whereMonth('date_don', now()->month)
                ->count(),
        ];
    }

    /**
     * Configuration de l'hôpital
     */
    private function getConfiguration(): array
    {
        return [
            'features' => $this->hopital->souscriptionActive?->getFeatures() ?? [],
            'max_users' => $this->hopital->souscriptionActive?->getMaxUsersForPlan() ?? 5,
        ];
    }
}