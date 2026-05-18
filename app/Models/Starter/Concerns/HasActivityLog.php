<?php

namespace App\Models\Starter\Concerns;

use App\Models\Starter\LogChange;
use App\Models\Starter\UserLogin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Throwable;

trait HasActivityLog
{
    /**
     * Snapshot before an update or delete happens.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $activityLogBefore = null;

    /**
     * Register automatic activity logging for this model.
     */
    protected static function bootHasActivityLog(): void
    {
        static::created(function (Model $model): void {
            $model->writeActivityLog('created', null, $model->activityLogAttributes(), [
                'changes' => $model->activityLogAttributes(),
            ]);
        });

        static::updating(function (Model $model): void {
            $model->activityLogBefore = $model->activityLogOriginalAttributes();
        });

        static::updated(function (Model $model): void {
            $changes = $model->activityLogChanges();

            if ($changes === []) {
                return;
            }

            $model->writeActivityLog('updated', $model->activityLogBefore, $model->activityLogAttributes(), [
                'changes' => $changes,
            ]);
        });

        static::deleting(function (Model $model): void {
            $model->activityLogBefore = $model->activityLogAttributes();
        });

        static::deleted(function (Model $model): void {
            $after = method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()
                ? $model->activityLogAttributes()
                : null;

            $model->writeActivityLog('deleted', $model->activityLogBefore, $after, [
                'changes' => $model->activityLogChanges(),
            ]);
        });
    }

    /**
     * Store one activity log row.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $payload
     */
    protected function writeActivityLog(string $action, ?array $before, ?array $after, array $payload = []): void
    {
        if (! $this->canWriteActivityLog()) {
            return;
        }

        try {
            LogChange::query()->create([
                'event' => $this->activityLogEvent($action),
                'action' => $action,
                'description' => $this->activityLogDescription($action),
                'loggable_type' => $this->getMorphClass(),
                'loggable_id' => $this->getKey(),
                'before' => $before,
                'after' => $after,
                'payload' => $payload === [] ? null : $payload,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'device' => $this->activityLogDevice(),
                'url' => request()?->fullUrl(),
                'method' => request()?->method(),
                'user_id' => $this->activityLogUserId(),
                'user_login_id' => $this->activityLogUserLoginId(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Determine whether the current runtime can safely write logs.
     */
    protected function canWriteActivityLog(): bool
    {
        try {
            return Schema::hasTable('log_changes');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Build the event code for this activity.
     */
    protected function activityLogEvent(string $action): string
    {
        return str(class_basename($this))->snake().'.'.$action;
    }

    /**
     * Build a readable activity sentence.
     */
    protected function activityLogDescription(string $action): string
    {
        $actor = auth()->user()?->name ?? 'System';
        $model = str(class_basename($this))->headline()->lower();

        return "{$actor} melakukan proses {$action} pada {$model}";
    }

    /**
     * Get a safe snapshot of the current model attributes.
     *
     * @return array<string, mixed>
     */
    protected function activityLogAttributes(): array
    {
        return $this->cleanActivityLogData($this->getAttributes());
    }

    /**
     * Get a safe snapshot of the original model attributes.
     *
     * @return array<string, mixed>
     */
    protected function activityLogOriginalAttributes(): array
    {
        return $this->cleanActivityLogData($this->getOriginal());
    }

    /**
     * Get changed values without noisy timestamp-only updates.
     *
     * @return array<string, mixed>
     */
    protected function activityLogChanges(): array
    {
        return $this->cleanActivityLogData(
            Arr::except($this->getChanges(), ['updated_at'])
        );
    }

    /**
     * Remove sensitive fields from log snapshots.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function cleanActivityLogData(array $data): array
    {
        return collect($data)
            ->reject(function (mixed $value, int|string $key): bool {
                return $this->isActivityLogHiddenKey((string) $key);
            })
            ->map(function (mixed $value): mixed {
                return is_array($value) ? $this->cleanActivityLogData($value) : $value;
            })
            ->all();
    }

    /**
     * Get fields that should never be stored in activity payloads.
     *
     * @return array<int, string>
     */
    protected function activityLogHidden(): array
    {
        $modelHidden = property_exists($this, 'activityLogHidden') && is_array($this->activityLogHidden)
            ? $this->activityLogHidden
            : [];

        return array_map('strval', array_values(array_unique(array_merge($this->getHidden(), $modelHidden, [
            'password',
            'password_confirmation',
            'remember_token',
            'current_password',
            'token',
            'api_token',
            'access_token',
            'refresh_token',
            'secret',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ]))));
    }

    /**
     * Determine whether an activity snapshot field should be hidden.
     */
    protected function isActivityLogHiddenKey(string $key): bool
    {
        $key = str($key)->lower()->toString();

        if (in_array($key, array_map('strtolower', $this->activityLogHidden()), true)) {
            return true;
        }

        return str_contains($key, 'password')
            || str_contains($key, 'secret')
            || str_ends_with($key, '_token');
    }

    /**
     * Resolve the current client id from the authenticated login.
     */
    protected function activityLogUserId(): ?int
    {
        $login = auth()->user();

        return $login instanceof UserLogin ? $login->user_id : null;
    }

    /**
     * Resolve the current login id from the authenticated login.
     */
    protected function activityLogUserLoginId(): ?int
    {
        $login = auth()->user();

        return $login instanceof UserLogin ? $login->id : null;
    }

    /**
     * Build a simple device summary from the request user agent.
     */
    protected function activityLogDevice(): ?string
    {
        $userAgent = request()?->userAgent();

        if (! $userAgent) {
            return null;
        }

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Browser',
        };

        $platform = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown Device',
        };

        return "{$browser} on {$platform}";
    }
}
