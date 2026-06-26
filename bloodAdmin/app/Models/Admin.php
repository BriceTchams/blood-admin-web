<?php 
// app/Models/Admin.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nom_complet',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}