<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterStation extends Model
{
    protected $fillable = ['name', 'ip_address', 'location', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
