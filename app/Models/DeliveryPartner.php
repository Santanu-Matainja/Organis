<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'photo', 'city',  
        'delivery_range', 'vehicle_type', 'license_number', 'status_id', 'state', 'zip_code', 'country_id'
    ];

}
