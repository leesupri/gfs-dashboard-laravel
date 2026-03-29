<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAuditLog extends Model
{
    protected $fillable = [
        'actor_staff_user_id',
        'target_staff_user_id',
        'action',
        'description',
    ];
}