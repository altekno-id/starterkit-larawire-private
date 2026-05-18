<?php

namespace App\Models\Starter;

use App\Models\Starter\Concerns\HasActivityLog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'email',
    'phone',
    'pic_name',
    'logo',
    'account_status',
    'approved_at',
    'subscription_status',
    'payment_method',
    'payment_reference',
    'trial_ends_at',
    'subscribed_at',
    'subscription_ends_at',
    'payment_approved_at',
])]
class User extends Model
{
    use HasActivityLog, SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'subscribed_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'payment_approved_at' => 'datetime',
        ];
    }

    /**
     * Get all login accounts belonging to this client.
     */
    public function logins(): HasMany
    {
        return $this->hasMany(UserLogin::class);
    }

    /**
     * Get all roles created by this client.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }
}
