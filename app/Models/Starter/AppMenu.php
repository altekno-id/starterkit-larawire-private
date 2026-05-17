<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppMenu extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'label',
        'icon',
        'order',
        'app_mod_id',
        'app_route_id',
        'parent_id',
    ];

    /**
     * Get the module that owns this menu.
     */
    public function mod(): BelongsTo
    {
        return $this->belongsTo(AppMod::class, 'app_mod_id');
    }

    /**
     * Get the route linked to this menu.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(AppRoute::class, 'app_route_id');
    }

    /**
     * Get this menu parent.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get this menu children.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
