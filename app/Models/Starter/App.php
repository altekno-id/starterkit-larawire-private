<?php

namespace App\Models\Starter;

use App\Models\Starter\Concerns\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends Model
{
    use HasActivityLog;

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
