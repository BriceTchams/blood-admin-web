<?php

namespace App\Jobs;

use App\Models\Hopital;
use App\Services\SyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Hopital $hopital)
    {
    }

    public function handle(SyncService $syncService): void
    {
        try {
            // Push local data
            $syncService->startPushSync($this->hopital);
            $data = $syncService->getPushData();
            // Send to server...

            // Pull server data
            $syncService->startPullSync($this->hopital);
            // Process pulled data...

        } catch (\Exception $e) {
            // \Log::error("Sync job failed for hospital {$this->hopital->id}: " . $e->getMessage());
        }
    }
}