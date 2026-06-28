<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'hopital_id',
        'type',
        'statut',
        'records_pushed',
        'records_pulled',
        'conflicts',
        'error_message',
        'started_at',
        'completed_at',
        'duration_seconds',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function hopital(): BelongsTo
    {
        return $this->belongsTo(Hopital::class);
    }

    public function markSuccess(int $pushed = 0, int $pulled = 0, int $conflicts = 0): void
    {
        $this->update([
            'statut' => 'success',
            'records_pushed' => $pushed,
            'records_pulled' => $pulled,
            'conflicts' => $conflicts,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'statut' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    public function markPartial(int $pushed, int $pulled, int $conflicts, string $error = null): void
    {
        $this->update([
            'statut' => 'partial',
            'records_pushed' => $pushed,
            'records_pulled' => $pulled,
            'conflicts' => $conflicts,
            'error_message' => $error,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }
}