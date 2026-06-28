<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hopital;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
        // $this->middleware('auth:sanctum');
    }

    /**
     * PUSH : Recevoir les données de l'app Hôpital
     * POST /api/sync/push
     */
    public function push(Request $request): JsonResponse
    {
        $hopital = Hopital::findOrFail($request->user()->hopital_id ?? $request->input('hopital_id'));

        // Vérifier que la licence est valide
        if (!$hopital->isLicenseValid()) {
            return response()->json([
                'success' => false,
                'message' => 'License expired',
            ], 403);
        }

        try {
            // Démarrer le log de sync
            $syncLog = $this->syncService->startPushSync($hopital);

            // Valider les données
            $validated = $request->validate([
                'donneurs' => 'nullable|array',
                'examens_pre_don' => 'nullable|array',
                'fiches_don' => 'nullable|array',
                'examens_post_don' => 'nullable|array',
            ]);

            // Traiter les données reçues
            $stats = $this->syncService->processPushData($validated);

            // Marquer comme succès
            $syncLog->markSuccess(
                $stats['created'] + $stats['updated'],
                0,
                $stats['conflicts']
            );

            return response()->json([
                'success' => true,
                'message' => 'Push synchronization successful',
                'stats' => $stats,
                'sync_log_id' => $syncLog->id,
            ], 200);

        } catch (\Exception $e) {
            // \Log::error('Sync push error: ' . $e->getMessage());

            $syncLog->markFailed($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Synchronization failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PULL : Envoyer les données à l'app Hôpital
     * GET /api/sync/pull
     */
    public function pull(Request $request): JsonResponse
    {
        $hopital = Hopital::findOrFail($request->user()->hopital_id ?? $request->input('hopital_id'));

        // Vérifier que la licence est valide
        if (!$hopital->isLicenseValid()) {
            return response()->json([
                'success' => false,
                'message' => 'License expired',
            ], 403);
        }

        try {
            // Démarrer le log de sync
            $syncLog = $this->syncService->startPullSync($hopital);

            // Récupérer les données
            $data = $this->syncService->getPullData();

            // Marquer comme succès
            $syncLog->markSuccess(0, count($data), 0);

            return response()->json([
                'success' => true,
                'message' => 'Pull synchronization successful',
                'data' => $data,
                'sync_log_id' => $syncLog->id,
            ], 200);

        } catch (\Exception $e) {
            // \Log::error('Sync pull error: ' . $e->getMessage());

            $syncLog->markFailed($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Synchronization failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET : Voir l'historique des syncs
     * GET /api/sync/history
     */
    public function history(Request $request): JsonResponse
    {
        $hopital = Hopital::findOrFail($request->user()->hopital_id ?? $request->input('hopital_id'));

        $logs = $hopital->syncLogs()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'type' => $log->type,
                'statut' => $log->statut,
                'records_pushed' => $log->records_pushed,
                'records_pulled' => $log->records_pulled,
                'conflicts' => $log->conflicts,
                'duration_seconds' => $log->duration_seconds,
                'started_at' => $log->started_at,
                'completed_at' => $log->completed_at,
                'error_message' => $log->error_message,
            ]);

        return response()->json([
            'success' => true,
            'hopital' => $hopital->nom,
            'history' => $logs,
        ]);
    }

    /**
     * GET : Voir la queue de sync
     * GET /api/sync/queue
     */
    public function queue(Request $request): JsonResponse
    {
        $hopital = Hopital::findOrFail($request->user()->hopital_id ?? $request->input('hopital_id'));

        $queued = $hopital->syncQueues()
            ->where('statut', '!=', 'synced')
            ->get()
            ->map(fn($q) => [
                'id' => $q->id,
                'table_name' => $q->table_name,
                'record_id' => $q->record_id,
                'operation' => $q->operation,
                'statut' => $q->statut,
                'attempt_count' => $q->attempt_count,
                'error_message' => $q->error_message,
                'queued_at' => $q->queued_at,
            ]);

        return response()->json([
            'success' => true,
            'pending_count' => $queued->count(),
            'queue' => $queued,
        ]);
    }

    /**
     * POST : Forcer une synchronisation complète
     * POST /api/sync/force
     */
    public function forceSync(Request $request): JsonResponse
    {
        $hopital = Hopital::findOrFail($request->user()->hopital_id ?? $request->input('hopital_id'));

        // Réinitialiser le timestamp de dernière synced
        $hopital->update(['last_synced_at' => now()->subYear()]);

        return response()->json([
            'success' => true,
            'message' => 'Next sync will be a full synchronization',
        ]);
    }

    /**
     * GET : Status de synchronisation
     * GET /api/sync/status
     */
    public function status(Request $request): JsonResponse
    {
        $hopital = Hopital::findOrFail($request->user()->hopital_id ?? $request->input('hopital_id'));

        $lastSync = $hopital->syncLogs()
            ->latest()
            ->first();

        $pendingQueue = $hopital->syncQueues()
            ->where('statut', 'pending')
            ->count();

        return response()->json([
            'success' => true,
            'hopital' => $hopital->nom,
            'status' => [
                'last_sync' => $lastSync,
                'pending_queue' => $pendingQueue,
                'is_online' => true,
                'license_valid' => $hopital->isLicenseValid(),
                'license_expires_at' => $hopital->license_expires_at,
            ],
        ]);
    }
}