<?php

namespace App\Models\Starter;

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
     * Get all roles created by this client.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(ClientRole::class);
    }
}
