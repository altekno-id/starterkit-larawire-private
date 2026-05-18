<?php

namespace App\Models\Starter;

use App\Models\Starter\Concerns\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppRoute extends Model
{
    use HasActivityLog;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'uri',
        'method',
        'app_mod_id',
    ];

    /**
     * Get the module that owns this route.
     */
    public function mod(): BelongsTo
    {
        return $this->belongsTo(AppMod::class, 'app_mod_id');
    }

    /**
     * Get all menus pointing to this route.
     */
    public function menus(): HasMany
    {
        return $this->hasMany(AppMenu::class);
    }
}
