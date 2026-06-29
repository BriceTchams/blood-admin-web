<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable 
{
    use HasUuids, HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'type',
        'login',
        'password_hash',
        'role',
        'telephone',
        'email',
         'name' ,
        'password' ,
        'uuid',
        'sync_statut',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];

    // ✅ Relations polymorphes
    public function userable()
    {
        return $this->morphTo();
    }

    public function hopital()
    {
        return $this->hasOne(Hopital::class, 'user_id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    // ✅ Accesseurs
    public function isHopital(): bool
    {
        return $this->type === 'hopital';
    }

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    // ✅ Scopes
    public function scopeHopitals($query)
    {
        return $query->where('type', 'hopital');
    }

    public function scopeAdmins($query)
    {
        return $query->where('type', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('deleted', false);
    }
    public function getFilamentName(): string
{
    return $this->name
        ?? $this->login
        ?? $this->email
        ?? 'Utilisateur';
}


public function getAuthPassword(): string
{
    return $this->password;
}
}
