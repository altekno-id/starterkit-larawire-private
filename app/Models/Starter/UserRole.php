<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserRole extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'desc',
    ];

    /**
     * Get the client that owns this role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all login accounts assigned to this role.
     */
    public function userLogins(): HasMany
    {
        return $this->hasMany(UserLogin::class);
    }

    /**
     * Get all modules allowed for this role.
     */
    public function mods(): BelongsToMany
    {
        return $this->belongsToMany(AppMod::class, 'rel_user_roles_app_mods')
            ->withTimestamps();
    }
}
