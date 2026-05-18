<?php

namespace App\Models\Starter;

use App\Models\Starter\Concerns\HasActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'user_id',
    'user_role_id',
    'name',
    'username',
    'email',
    'email_verified_at',
    'password',
    'google_id',
    'google_avatar',
    'last_login_at',
    'last_login_ip',
    'last_login_provider',
    'remember_token',
])]
#[Hidden(['password', 'remember_token'])]
class UserLogin extends Authenticatable
{
    use HasActivityLog, Notifiable;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the client that owns this login account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role assigned to this login account.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    /**
     * Determine if this login can access a module.
     */
    public function canAccessMod(AppMod|string|int $mod): bool
    {
        return $this->role?->canAccessMod($mod) ?? false;
    }

    /**
     * Determine if this login can access a named route.
     */
    public function canAccessRoute(AppRoute|string $route): bool
    {
        return $this->role?->canAccessRoute($route) ?? false;
    }
}
