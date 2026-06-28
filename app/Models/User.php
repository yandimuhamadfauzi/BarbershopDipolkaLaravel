<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = [
        'nama',
        'email',
        'password',
        'foto',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_admin'   => 'boolean',
        'password'   => 'hashed',
    ];

    public function antrian(): HasMany
    {
        return $this->hasMany(Antrian::class, 'user_id');
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && file_exists(public_path('img/profil/' . $this->foto))) {
            return asset('img/profil/' . $this->foto);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nama) . '&background=d4af37&color=000&bold=true';
    }
}
