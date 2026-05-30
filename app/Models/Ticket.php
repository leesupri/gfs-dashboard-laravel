<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'staff_user_id',
        'submitter_name',
        'submitter_email',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    const CATEGORY_LABELS = [
        'general'   => 'General Inquiry',
        'technical' => 'Technical Issue',
        'access'    => 'Access Request',
        'report'    => 'Report Issue',
        'feature'   => 'Feature Request',
        'other'     => 'Other',
    ];

    const PRIORITY_LABELS = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
        'urgent' => 'Urgent',
    ];

    const STATUS_LABELS = [
        'open'        => 'Open',
        'in_progress' => 'In Progress',
        'resolved'    => 'Resolved',
        'closed'      => 'Closed',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'staff_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    // ── Ticket number generator ────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $prefix = 'TKT-' . now()->format('Ym') . '-';

        $last = static::where('ticket_number', 'like', $prefix . '%')
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        if ($last) {
            $seq = (int) substr($last, strlen($prefix));
            $seq++;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Badge helpers ─────────────────────────────────────────────────────────

    public function statusBadge(): string
    {
        return match ($this->status) {
            'open'        => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-amber-100 text-amber-700',
            'resolved'    => 'bg-green-100 text-green-700',
            'closed'      => 'bg-gray-100 text-gray-600',
            default       => 'bg-gray-100 text-gray-600',
        };
    }

    public function priorityBadge(): string
    {
        return match ($this->priority) {
            'low'    => 'bg-gray-100 text-gray-600',
            'medium' => 'bg-blue-100 text-blue-700',
            'high'   => 'bg-amber-100 text-amber-700',
            'urgent' => 'bg-red-100 text-red-700',
            default  => 'bg-gray-100 text-gray-600',
        };
    }
}
