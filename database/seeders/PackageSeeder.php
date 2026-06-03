<?php

namespace Database\Seeders;

use App\Models\Starter\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        self::syncDefaults();
    }

    public static function syncDefaults(): void
    {
        $codes = collect(self::defaults())->pluck('code')->all();

        foreach (self::defaults() as $package) {
            Package::query()->updateOrCreate([
                'code' => $package['code'],
            ], $package);
        }

        Package::query()
            ->whereNotIn('code', $codes)
            ->whereIn('code', ['free'])
            ->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function defaults(): array
    {
        return [
            [
                'code' => 'trial',
                'name' => 'Trial',
                'desc' => 'Temporary access for teams that want to evaluate the product first.',
                'type' => 'trial',
                'price' => 0,
                'setup_fee' => 0,
                'billing_cycle' => 'none',
                'trial_days' => 14,
                'features' => [
                    '14 days trial access',
                    'Multi app preview',
                    'Role and module testing',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'business',
                'name' => 'Business',
                'desc' => 'Monthly package for production SaaS clients with growing teams.',
                'type' => 'paid',
                'price' => 99000,
                'setup_fee' => 0,
                'billing_cycle' => 'monthly',
                'trial_days' => null,
                'features' => [
                    'Multi user and role management',
                    'All registered apps',
                    'Priority product updates',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'enterprise',
                'name' => 'Enterprise',
                'desc' => 'Custom deployment package for private or corporate installs.',
                'type' => 'custom',
                'price' => 0,
                'setup_fee' => 2500000,
                'billing_cycle' => 'custom',
                'trial_days' => null,
                'features' => [
                    'Private deployment support',
                    'Custom app onboarding',
                    'Advanced SLA options',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];
    }
}
