<?php

namespace App\Models\Starter;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'desc',
    'type',
    'price',
    'setup_fee',
    'billing_cycle',
    'trial_days',
    'features',
    'is_active',
    'sort_order',
])]
class Package extends Model
{
    protected $table = 'x_packages';

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'bool',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function priceLabel(): string
    {
        if ($this->type === 'custom') {
            return 'Custom';
        }

        if ($this->price <= 0) {
            return 'Free';
        }

        return 'Rp '.number_format((int) $this->price, 0, ',', '.');
    }

    public function billingLabel(): string
    {
        return match ($this->billing_cycle) {
            'none' => 'No recurring billing',
            'once' => 'One-time payment',
            'monthly' => 'per month',
            'yearly' => 'per year',
            default => 'custom billing',
        };
    }

    public function setupFeeLabel(): string
    {
        if ($this->setup_fee <= 0) {
            return 'No setup fee';
        }

        return 'Setup Rp '.number_format((int) $this->setup_fee, 0, ',', '.');
    }
}
