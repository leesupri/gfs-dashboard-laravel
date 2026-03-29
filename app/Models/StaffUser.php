<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffUser extends Model
{
    protected $fillable = [
        'name',
        'username',
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

    public function hasAccess(string $routeName): bool
    {
        if ($routeName === '') {
            return false;
        }

        return $this->permissions()
            ->where('route_name', $routeName)
            ->where('can_view', true)
            ->exists();
    }
}