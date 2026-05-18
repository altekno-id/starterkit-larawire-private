<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'event',
    'action',
    'description',
    'loggable_type',
    'loggable_id',
    'before',
    'after',
    'payload',
    'ip_address',
    'user_agent',
    'device',
    'url',
    'method',
    'user_id',
    'user_login_id',
    'created_at',
])]
#[WithoutTimestamps]
class LogChange extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the model affected by this activity.
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the client that owns this activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the login account that performed this activity.
     */
    public function userLogin(): BelongsTo
    {
        return $this->belongsTo(UserLogin::class);
    }
}
