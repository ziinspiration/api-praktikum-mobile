<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use HasFactory;

    protected $table = 'dealer';

    protected $fillable = ['nama', 'alamat', 'kontak'];

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'dealer_id');
    }
}
