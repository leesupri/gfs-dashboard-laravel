<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPageLog extends Model
{
    protected $fillable = [
        'staff_user_id',
        'staff_name',
        'route_name',
        'url',
        'method',
        'ip_address',
        'status_code',
        'error_message',
    ];

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'staff_user_id');
    }

    public function isError(): bool
    {
        return $this->status_code >= 400;
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->status_code >= 500 => 'Server Error',
            $this->status_code >= 400 => 'Client Error',
            $this->status_code >= 200 => 'OK',
            default                   => (string) $this->status_code,
        };
    }
}
