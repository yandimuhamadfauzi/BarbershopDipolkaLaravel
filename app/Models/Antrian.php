<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Antrian extends Model
{
    protected $table = 'antrian';

    protected $fillable = [
        'user_id',
        'kapster_id',
        'nama',
        'layanan',
        'harga',
        'nomor_antrian',
        'status',
        'tanggal_booking',
        'jam_booking',
        'waktu_selesai',
        'notif',
        'payment_status',
        'snap_token',
    ];

    protected $casts = [
        'tanggal_booking' => 'date',
        'harga'           => 'integer',
        'nomor_antrian'   => 'integer',
        'notif'           => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kapster(): BelongsTo
    {
        return $this->belongsTo(Kapster::class, 'kapster_id');
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'Menunggu'  => 'warning',
            'Dipanggil' => 'info',
            'Selesai'   => 'success',
            'Batal'     => 'danger',
            default     => 'secondary',
        };
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['Menunggu', 'Dipanggil']);
    }
}
