<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'starter_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'actor_is_superuser' => 'boolean',
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function login(): BelongsTo
    {
        return $this->belongsTo(ClientLogin::class, 'client_login_id');
    }
}
