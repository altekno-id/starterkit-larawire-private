<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'email',
    'package_id',
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
class Client extends Model
{
    use SoftDeletes;

    protected $table = 'starter_clients';

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
        return $this->hasMany(ClientLogin::class);
    }

    /**
     * Get the package selected by this client.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get all roles created by this client.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(ClientRole::class);
    }
}
