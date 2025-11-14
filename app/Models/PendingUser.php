<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',	'email',	'password',	'bactive',	'status_id',	'role_id'	,'otp_code'	,'otp_expires_at','shop_name' ,
			'shop_url' ,
			'phone' ,
			'address',
			'vat_number' ,
			'trade_register_number' ,
    ];
    
}
