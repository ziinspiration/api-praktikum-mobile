<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'booking';

    protected $fillable = [
        'user_id',
        'mobil_id',
        'dealer_id',
        'tanggal',
        'waktu',
        'status'
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'waktu' => 'datetime:H:i:s',
    ];

    public function mobil()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}