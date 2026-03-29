<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityPermission extends Model
{
    protected $fillable = [
        'staff_user_id',
        'route_name',
        'can_view',
    ];

    protected $casts = [
        'can_view' => 'boolean',
    ];

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class);
    }
}