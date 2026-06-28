<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncQueue extends Model
{
    use HasUuids;

    protected $fillable = [
        'hopital_id',
        'table_name',
        'record_id',
        'operation',
        'payload',
        'statut',
        'attempt_count',
        'queued_at',
        'synced_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'queued_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function hopital(): BelongsTo
    {
        return $this->belongsTo(Hopital::class);
    }

    public function markSynced(): void
    {
        $this->update([
            'statut' => 'synced',
            'synced_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->increment('attempt_count');
        $this->update([
            'statut' => 'failed',
            'error_message' => $error,
        ]);
    }

    public function markConflict(): void
    {
        $this->update(['statut' => 'conflict']);
    }

    public function isStale(): bool
    {
        return $this->created_at->addHours(24)->lt(now());
    }
}