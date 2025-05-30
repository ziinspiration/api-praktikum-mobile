<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute(): ?string
    {
        if ($this->gambar) {
            if (filter_var($this->gambar, FILTER_VALIDATE_URL)) {
                return $this->gambar;
            }
            return asset('storage/' . $this->gambar);
        }
        return null;
    }

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'mobil_id');
    }
}
