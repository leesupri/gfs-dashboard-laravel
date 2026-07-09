<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    protected $connection = 'mysql';
    protected $table = 'stock_alerts';

    protected $fillable = [
        'item_id', 'item_name', 'warehouse_id', 'alert_type',
        'threshold_value', 'is_active', 'created_by',
    ];

    protected $casts = [
        'threshold_value' => 'float',
        'is_active'       => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'created_by');
    }
}
