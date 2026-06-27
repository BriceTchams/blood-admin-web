<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class License extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'hopital_id',
        'license_key',
        'plan',
        'max_users',
        'created_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relation
    public function hopital(): BelongsTo
    {
        return $this->belongsTo(Hopital::class);
    }

    // Méthodes
    public function isValid(): bool
    {
        return $this->status === 'active' && now()->lt($this->expires_at);
    }

    public function isExpired(): bool
    {
        return now()->gt($this->expires_at);
    }

    public function daysRemaining(): int
    {
        return max(0, now()->diffInDays($this->expires_at));
    }

    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }
}