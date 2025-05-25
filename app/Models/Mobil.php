<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    use HasFactory;

    protected $table = 'mobil';

    protected $fillable = [
        'nama',
        'tipe',
        'tahun',
        'harga',
        'mesin',
        'transmisi',
        'kapasitas_bensin',
        'warna',
        'fitur_lain',
        'deskripsi',
        'gambar',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'harga' => 'decimal:2',
    ];

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'mobil_id');
    }
}