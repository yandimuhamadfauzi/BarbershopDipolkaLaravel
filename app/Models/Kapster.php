<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kapster extends Model
{
    protected $table = 'kapsters';

    protected $fillable = [
        'nama',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function antrian(): HasMany
    {
        return $this->hasMany(Antrian::class, 'kapster_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
