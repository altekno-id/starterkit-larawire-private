<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'client_id',
    'client_role_id',
    'name',
    'username',
    'email',
    'email_verified_at',
    'password',
    'profile_photo',
    'status',
    'must_change_password',
    'password_changed_at',
    'failed_login_count',
    'locked_until',
    'last_login_at',
    'last_login_ip',
    'remember_token',
])]
#[Hidden(['password', 'remember_token'])]
class ClientLogin extends Authenticatable
{
    use Notifiable;

    protected $table = 'starter_client_logins';

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
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->locked_until === null || $this->locked_until->isPast());
    }

    /**
     * Get the client that owns this login account.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the role assigned to this login account.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(ClientRole::class, 'client_role_id');
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
