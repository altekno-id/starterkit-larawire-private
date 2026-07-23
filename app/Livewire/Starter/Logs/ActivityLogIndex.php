<?php

namespace App\Livewire\Starter\Logs;

use App\Models\Starter\ActivityLog;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
#[Title('Log Aktivitas')]
class ActivityLogIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $eventFilter = '';

    public string $actorFilter = '';

    public string $roleFilter = '';

    public string $appFilter = '';

    public string $tableFilter = '';

    public string $routeFilter = '';

    public string $ipFilter = '';

    public string $actionFilter = '';

    public int $perPage = 25;

    public bool $advancedFiltersOpen = false;

    public bool $detailModalOpen = false;

    public ?string $selectedActionId = null;

    public function mount(): void
    {
        $this->login();
        $this->setDefaultDates();
    }

    public function updated(string $property): void
    {
        if ($property === 'perPage' && ! in_array($this->perPage, [25, 50, 100], true)) {
            $this->perPage = 25;
        }

        if (in_array($property, $this->filterProperties(), true)) {
            $this->resetPage('logsPage');
        }
    }

    public function toggleAdvancedFilters(): void
    {
        $this->advancedFiltersOpen = ! $this->advancedFiltersOpen;
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'eventFilter',
            'actorFilter',
            'roleFilter',
            'appFilter',
            'tableFilter',
            'routeFilter',
            'ipFilter',
            'actionFilter',
        ]);
        $this->perPage = 25;
        $this->advancedFiltersOpen = false;
        $this->setDefaultDates();
        $this->resetPage('logsPage');
    }

    public function showActionDetail(string $actionId): void
    {
        abort_unless(preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $actionId) === 1, 404);
        abort_unless($this->viewerQuery()->where('action_id', $actionId)->exists(), 404);

        $this->selectedActionId = $actionId;
        $this->detailModalOpen = true;
    }

    public function closeActionDetail(): void
    {
        $this->detailModalOpen = false;
        $this->selectedActionId = null;
    }

    public function render()
    {
        $login = $this->login();
        $filteredQuery = $this->filteredQuery();
        $actionPage = (clone $filteredQuery)
            ->select('action_id')
            ->selectRaw('MAX(created_at) as last_activity_at')
            ->selectRaw('COUNT(*) as changes_count')
            ->selectRaw('COUNT(DISTINCT table_name) as tables_count')
            ->groupBy('action_id')
            ->orderByDesc('last_activity_at')
            ->paginate($this->perPage, ['*'], 'logsPage');

        $actionIds = $actionPage->getCollection()->pluck('action_id');
        $entries = $actionIds->isEmpty()
            ? collect()
            : $this->viewerQuery()
                ->whereIn('action_id', $actionIds)
                ->orderBy('sequence')
                ->orderBy('id')
                ->get()
                ->groupBy('action_id');

        $actionPage->setCollection($actionPage->getCollection()->map(
            fn (ActivityLog $summary): array => $this->actionSummary($summary, $entries->get($summary->action_id, collect()), $login),
        ));

        $allLogs = $this->viewerQuery();
        $selectedLogs = $this->selectedActionId
            ? $this->viewerQuery()
                ->where('action_id', $this->selectedActionId)
                ->orderBy('sequence')
                ->orderBy('id')
                ->get()
            : collect();

        return view('livewire.starter.logs.activity-log-index', [
            'actions' => $actionPage,
            'selectedLogs' => $selectedLogs,
            'filterOptions' => $this->filterOptions($login),
            'totalChanges' => (clone $allLogs)->count(),
            'todayChanges' => (clone $allLogs)->whereDate('created_at', today())->count(),
            'activeActorCount' => (clone $allLogs)->whereNotNull('client_login_id')->distinct('client_login_id')->count('client_login_id'),
        ]);
    }

    /**
     * @param  Collection<int, ActivityLog>  $entries
     * @return array<string, mixed>
     */
    private function actionSummary(ActivityLog $summary, Collection $entries, ClientLogin $viewer): array
    {
        $first = $entries->first();
        $actorIsMasked = ! $viewer->role?->isSuperuser() && ($first?->actor_is_superuser ?? false);

        return [
            'action_id' => $summary->action_id,
            'created_at' => $first?->created_at,
            'action_key' => $first?->action_key,
            'action_label' => $first?->action_label ?: 'Aktivitas data',
            'actor_name' => $actorIsMasked ? 'Sistem' : ($first?->actor_name ?: 'User tidak tersedia'),
            'actor_username' => $actorIsMasked ? null : $first?->actor_username,
            'actor_role' => $actorIsMasked ? 'Sistem' : ($first?->actor_role ?: '-'),
            'changes_count' => (int) $summary->changes_count,
            'tables_count' => (int) $summary->tables_count,
            'events' => $entries->pluck('event')->filter()->unique()->values()->all(),
            'app_key' => $first?->app_key,
            'route_name' => $first?->route_name,
            'ip_address' => $first?->ip_address,
            'source' => $first?->source,
        ];
    }

    /**
     * @return array{actors: Collection<int, ClientLogin>, roles: Collection<int, string>, apps: Collection<int, string>, tables: Collection<int, string>, routes: Collection<int, string>}
     */
    private function filterOptions(ClientLogin $login): array
    {
        $base = $this->viewerQuery();
        $actors = ClientLogin::query()
            ->with('role')
            ->when(! $login->role?->isSuperuser(), fn (Builder $query): Builder => $query->whereHas('role', fn (Builder $roleQuery): Builder => $roleQuery->where('is_system', false)))
            ->orderBy('name')
            ->get(['id', 'client_role_id', 'name', 'username']);

        return [
            'actors' => $actors,
            'roles' => $this->distinctOptions(clone $base, 'actor_role'),
            'apps' => $this->distinctOptions(clone $base, 'app_key'),
            'tables' => $this->distinctOptions(clone $base, 'table_name'),
            'routes' => $this->distinctOptions(clone $base, 'route_name'),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function distinctOptions(Builder $query, string $column): Collection
    {
        return $query
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->values();
    }

    private function filteredQuery(): Builder
    {
        $query = $this->viewerQuery();
        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('action_label', 'like', "%{$search}%")
                    ->orWhere('action_key', 'like', "%{$search}%")
                    ->orWhere('action_id', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_username', 'like', "%{$search}%")
                    ->orWhere('auditable_label', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        return $query
            ->when($this->validDate($this->dateFrom), fn (Builder $dateQuery): Builder => $dateQuery->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->validDate($this->dateTo), fn (Builder $dateQuery): Builder => $dateQuery->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->eventFilter !== '', fn (Builder $eventQuery): Builder => $eventQuery->where('event', $this->eventFilter))
            ->when($this->actorFilter !== '', fn (Builder $actorQuery): Builder => $actorQuery->where('client_login_id', (int) $this->actorFilter))
            ->when($this->roleFilter !== '', fn (Builder $roleQuery): Builder => $roleQuery->where('actor_role', $this->roleFilter))
            ->when($this->appFilter !== '', fn (Builder $appQuery): Builder => $appQuery->where('app_key', $this->appFilter))
            ->when($this->tableFilter !== '', fn (Builder $tableQuery): Builder => $tableQuery->where('table_name', $this->tableFilter))
            ->when($this->routeFilter !== '', fn (Builder $routeQuery): Builder => $routeQuery->where('route_name', $this->routeFilter))
            ->when(trim($this->ipFilter) !== '', fn (Builder $ipQuery): Builder => $ipQuery->where('ip_address', 'like', '%'.trim($this->ipFilter).'%'))
            ->when(trim($this->actionFilter) !== '', fn (Builder $actionQuery): Builder => $actionQuery->where('action_id', 'like', '%'.trim($this->actionFilter).'%'));
    }

    private function viewerQuery(): Builder
    {
        $login = $this->login();
        $query = ActivityLog::query();

        if ($login->role?->isSuperuser()) {
            return $query;
        }

        $protectedRoleIds = ClientRole::query()
            ->where(function (Builder $roleQuery): void {
                $roleQuery->where('is_system', true)->orWhere('code', 'superuser');
            })
            ->pluck('id')
            ->map(fn (int|string $id): string => (string) $id)
            ->all();
        $protectedUserIds = ClientLogin::query()
            ->whereIn('client_role_id', $protectedRoleIds)
            ->pluck('id')
            ->map(fn (int|string $id): string => (string) $id)
            ->all();

        return $query->whereNot(function (Builder $protectedQuery) use ($protectedRoleIds, $protectedUserIds): void {
            $protectedQuery
                ->when($protectedRoleIds !== [], function (Builder $roleQuery) use ($protectedRoleIds): void {
                    $roleQuery->where(function (Builder $targetQuery) use ($protectedRoleIds): void {
                        $targetQuery
                            ->where('auditable_type', ClientRole::class)
                            ->whereIn('auditable_id', $protectedRoleIds);
                    });
                })
                ->when($protectedUserIds !== [], function (Builder $userQuery) use ($protectedRoleIds, $protectedUserIds): void {
                    $method = $protectedRoleIds === [] ? 'where' : 'orWhere';
                    $userQuery->{$method}(function (Builder $targetQuery) use ($protectedUserIds): void {
                        $targetQuery
                            ->where('auditable_type', ClientLogin::class)
                            ->whereIn('auditable_id', $protectedUserIds);
                    });
                });
        });
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();

        abort_unless(
            $login instanceof ClientLogin
                && ($login->loadMissing('role')->role?->canViewLogs() ?? false),
            403,
        );

        return $login;
    }

    private function setDefaultDates(): void
    {
        $this->dateFrom = now()->subDays(29)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    private function validDate(string $date): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
    }

    /**
     * @return list<string>
     */
    private function filterProperties(): array
    {
        return [
            'search',
            'dateFrom',
            'dateTo',
            'eventFilter',
            'actorFilter',
            'roleFilter',
            'appFilter',
            'tableFilter',
            'routeFilter',
            'ipFilter',
            'actionFilter',
            'perPage',
        ];
    }
}
