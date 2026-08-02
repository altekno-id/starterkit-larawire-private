<?php

namespace Altekno\StarterKit\Livewire\Starter\Logs;

use Altekno\StarterKit\Contracts\Starter\ActivityLogInterface;
use Altekno\StarterKit\Models\Starter\ActivityLog;
use Altekno\StarterKit\Models\Starter\ClientLogin;
use Altekno\StarterKit\Services\Starter\AuthenticatedLoginService;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

class ActivityLogsTable extends PowerGridComponent
{
    public string $tableName = 'starter-activity-logs-table';

    public string $primaryKey = 'action_id';

    private ActivityLogInterface $activityLogs;

    private AuthenticatedLoginService $authenticatedLogins;

    public function boot(ActivityLogInterface $activityLogs, AuthenticatedLoginService $authenticatedLogins): void
    {
        $this->activityLogs = $activityLogs;
        $this->authenticatedLogins = $authenticatedLogins;
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage(25, [25, 50, 100])->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return $this->activityLogs->tableQueryForViewer($this->login());
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('action_id')->add('action_label')->add('action_key')->add('actor_name')->add('actor_role')
            ->add('app_key')->add('route_name')->add('ip_address')->add('changes_count')->add('tables_count')
            ->add('created_at')
            ->add('created_at_label', fn (ActivityLog $log): string => $log->created_at?->format('d M Y H:i:s') ?? '-');
    }

    public function columns(): array
    {
        return [
            Column::make('Waktu', 'created_at_label', 'created_at')->sortable(),
            Column::make('Aktivitas', 'action_label')->searchable()->sortable(),
            Column::make('Aktor', 'actor_name')->searchable()->sortable(),
            Column::make('Role', 'actor_role')->searchable()->sortable(),
            Column::make('Aplikasi', 'app_key')->searchable()->sortable(),
            Column::make('Route', 'route_name')->searchable()->sortable(),
            Column::make('IP', 'ip_address')->searchable()->sortable(),
            Column::make('Perubahan', 'changes_count')->sortable(),
            Column::make('Tabel', 'tables_count')->sortable(),
            Column::action('Detail'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('created_at_label', 'created_at')
                ->operators(['starts_with'])
                ->placeholder('YYYY-MM-DD'),
            Filter::inputText('action_label')->operators(['contains']),
            Filter::inputText('actor_name')->operators(['contains']),
            Filter::inputText('actor_role')->operators(['contains']),
            Filter::inputText('app_key')->operators(['contains']),
            Filter::inputText('route_name')->operators(['contains']),
            Filter::inputText('ip_address')->operators(['starts_with']),
        ];
    }

    public function actions(ActivityLog $row): array
    {
        return [
            Button::add('detail')->slot('Lihat')->dispatch('starter-log-detail-request', ['actionId' => $row->action_id])->class('btn btn-sm btn-outline-primary'),
        ];
    }

    private function login(): ClientLogin
    {
        return $this->authenticatedLogins->logViewer();
    }
}
