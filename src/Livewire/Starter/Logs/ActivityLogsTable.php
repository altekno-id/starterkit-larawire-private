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

    public bool $showFilters = true;

    private ActivityLogInterface $activityLogs;

    private AuthenticatedLoginService $authenticatedLogins;

    public function boot(ActivityLogInterface $activityLogs, AuthenticatedLoginService $authenticatedLogins): void
    {
        $this->activityLogs = $activityLogs;
        $this->authenticatedLogins = $authenticatedLogins;
    }

    public function setUp(): array
    {
        $this->persist(['filters', 'sorting']);

        return [
            PowerGrid::header()->includeViewOnTop('starter.logs.powergrid.activity-logs-toolbar'),
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
            Column::action('Aksi'),
            Column::make('Waktu', 'created_at_label', 'created_at')->sortable(),
            Column::make('Aktivitas', 'action_label')->searchable()->sortable(),
            Column::make('Aktor', 'actor_name')->searchable()->sortable(),
            Column::make('Role', 'actor_role')->searchable()->sortable(),
            Column::make('Aplikasi', 'app_key')->searchable()->sortable(),
            Column::make('Route', 'route_name')->searchable()->sortable(),
            Column::make('IP', 'ip_address')->searchable()->sortable(),
            Column::make('Perubahan', 'changes_count')->sortable(),
            Column::make('Tabel', 'tables_count')->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datetimepicker('created_at_label', 'created_at')->params(['mode' => 'range']),
            Filter::inputText('action_label')->operators(['contains']),
            Filter::inputText('actor_name')->operators(['contains']),
            Filter::inputText('actor_role')->operators(['contains']),
            Filter::inputText('app_key')->operators(['contains']),
            Filter::inputText('route_name')->operators(['contains']),
            Filter::inputText('ip_address')->operators(['starts_with']),
            Filter::number('changes_count')->placeholder('Min', 'Max'),
            Filter::number('tables_count')->placeholder('Min', 'Max'),
        ];
    }

    public function actionsFromView(ActivityLog $row): \Illuminate\View\View
    {
        return view('starter.logs.powergrid.logs-row-actions', ['row' => $row]);
    }

    private function login(): ClientLogin
    {
        return $this->authenticatedLogins->logViewer();
    }
}
