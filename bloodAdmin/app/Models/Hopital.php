<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;  // ← BON import

class Hopital extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nom',
        'ville',
        'adresse',
        'logo',
        'email',
        'telephone_principal',
        'statut',
        'license_expires_at',
        'code_hopital',
        'sync_statut',
        'last_synced_at',
    ];

    protected $casts = [
        'license_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'deleted' => 'boolean',
    ];

    // ✅ Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function donneurs(): HasMany
    {
        return $this->hasMany(Donneur::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function souscriptions(): HasMany
    {
        return $this->hasMany(Souscription::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    public function syncQueues(): HasMany
    {
        return $this->hasMany(SyncQueue::class);
    }

    public function license()
    {
        return $this->hasOne(License::class);
    }

    public function souscriptionActive()
    {
        return $this->hasOne(Souscription::class)
            ->where('statut', 'active')
            ->latestOfMany();
    }

    // ✅ Accesseurs
    public function isActive(): bool
    {
        return $this->statut === 'active' && !$this->deleted;
    }

    public function isLicenseValid(): bool
    {
        if (!$this->license_expires_at) {
            return false;
        }

        return now()->lessThan($this->license_expires_at);
    }

    // ✅ Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'active')
            ->where('deleted', false);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code_hopital', $code);
    }
}