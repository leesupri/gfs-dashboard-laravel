<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterStation extends Model
{
    protected $fillable = ['name', 'ip_address', 'location', 'is_active', 'is_auto_cut'];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_auto_cut' => 'boolean',
    ];
}
