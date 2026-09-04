<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id', 'courier', 'tracking_number', 'status', 'shipped_at'
    ];

    public function order() { return $this->belongsTo(Order::class); }
}