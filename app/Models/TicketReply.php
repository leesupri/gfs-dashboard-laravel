<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'staff_user_id',
        'reply_by_name',
        'message',
        'is_staff_reply',
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'staff_user_id');
    }
}
