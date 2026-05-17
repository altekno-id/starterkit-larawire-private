<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'subdomain',
        'desc',
        'icon',
    ];

    /**
     * Get all modules registered for this app.
     */
    public function mods(): HasMany
    {
        return $this->hasMany(AppMod::class);
    }
}
