<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class StaffUser extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'title',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(SecurityPermission::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function stockCounts(): HasMany
    {
        return $this->hasMany(StockCount::class, 'created_by');
    }

    public function approvedCounts(): HasMany
    {
        return $this->hasMany(StockCount::class, 'approved_by');
    }

    public function hasAccess(string $routeName): bool
    {
        if ($routeName === '') {
            return false;
        }

        // Use the in-memory collection when permissions are already eager-loaded
        // (StaffAuth middleware loads them once per request), avoiding N+1 queries.
        if ($this->relationLoaded('permissions')) {
            return $this->permissions
                ->where('route_name', $routeName)
                ->where('can_view', true)
                ->isNotEmpty();
        }

        return $this->permissions()
            ->where('route_name', $routeName)
            ->where('can_view', true)
            ->exists();
    }
}
