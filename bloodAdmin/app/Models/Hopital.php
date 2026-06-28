<?php
// app/Models/Hopital.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // public function donneurs()
    // {
    //     return $this->hasMany(Donneur::class);
    // }

    // public function notifications()
    // {
    //     return $this->hasMany(Notification::class);
    // }

    // public function souscriptions()
    // {
    //     return $this->hasMany(Souscription::class);
    // }

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