<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountLine extends Model
{
    protected $connection = 'mysql';
    protected $table = 'stock_count_lines';

    protected $fillable = [
        'stock_count_id', 'item_id', 'item_code', 'item_name', 'item_uom',
        'qty_entered', 'uom_entered', 'qty_in_base_uom', 'notes',
    ];

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }
}
