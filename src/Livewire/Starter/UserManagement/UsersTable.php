<?php

namespace Altekno\StarterKit\Livewire\Starter\UserManagement;

use Altekno\StarterKit\Models\Starter\ClientLogin;
use Altekno\StarterKit\Services\Starter\AuthenticatedLoginService;
use Altekno\StarterKit\Services\Starter\UserManagementUserService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

class UsersTable extends PowerGridComponent
{
    public string $tableName = 'starter-users-table';

    public string $archiveStatus = 'active';

    public ?string $pendingAction = null;

    /** @var list<int> */
    public array $pendingIds = [];

    private UserManagementUserService $users;

    private AuthenticatedLoginService $authenticatedLogins;

    public function boot(UserManagementUserService $users, AuthenticatedLoginService $authenticatedLogins): void
    {
        $this->users = $users;
        $this->authenticatedLogins = $authenticatedLogins;
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()->showSearchInput()->includeViewOnTop('starter.user-management.powergrid.users-toolbar'),
            PowerGrid::footer()->showPerPage(10, [10, 25, 50, 100])->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return $this->users->tableQuery($this->login(), $this->archiveStatus);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('username')
            ->add('email')
            ->add('role_name')
            ->add('status')
            ->add('status_label', fn (ClientLogin $login): string => match ($login->status) {
                'active' => 'Aktif',
                'inactive' => 'Nonaktif',
                'locked' => 'Terkunci',
                default => ucfirst($login->status),
            })
            ->add('last_login_at')
            ->add('last_login_label', fn (ClientLogin $login): string => $login->last_login_at?->format('d M Y H:i') ?? 'Belum pernah')
            ->add('deleted_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Nama', 'name', 'starter_client_logins.name')->searchable()->sortable(),
            Column::make('Username', 'username', 'starter_client_logins.username')->searchable()->sortable(),
            Column::make('Email', 'email', 'starter_client_logins.email')->searchable()->sortable(),
            Column::make('Role', 'role_name', 'list_role.name')->searchable()->sortable(),
            Column::make('Status', 'status_label', 'starter_client_logins.status')->sortable(),
            Column::make('Login terakhir', 'last_login_label', 'starter_client_logins.last_login_at')->sortable(),
            Column::action('Aksi'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name', 'starter_client_logins.name')->operators(['contains']),
            Filter::inputText('username', 'starter_client_logins.username')->operators(['contains']),
            Filter::inputText('email', 'starter_client_logins.email')->operators(['contains']),
            Filter::inputText('role_name', 'list_role.name')->operators(['contains']),
            Filter::select('status_label', 'starter_client_logins.status')->dataSource([
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'inactive', 'label' => 'Nonaktif'],
                ['value' => 'locked', 'label' => 'Terkunci'],
            ])->optionValue('value')->optionLabel('label'),
            Filter::datetimepicker('last_login_label', 'starter_client_logins.last_login_at'),
        ];
    }

    public function actions(ClientLogin $row): array
    {
        $buttons = [];

        if (! $row->trashed()) {
            $buttons[] = Button::add('edit')->slot('Edit')->route('starter.user-management.users.edit', ['userLoginId' => $row->id])->class('btn btn-sm btn-outline-primary');

            if (! (bool) $row->role_is_system && $row->role_code !== 'superuser') {
                $buttons[] = Button::add('reset-password')->slot('Reset password')->dispatch('starter-user-reset-request', ['id' => $row->id])->class('btn btn-sm btn-outline-secondary');
                $buttons[] = Button::add('archive')->slot('Arsipkan')->dispatchSelf('prepare-row-action', ['action' => 'archive', 'id' => $row->id])->class('btn btn-sm btn-outline-warning');
            }
        } else {
            $buttons[] = Button::add('restore')->slot('Pulihkan')->dispatchSelf('prepare-row-action', ['action' => 'restore', 'id' => $row->id])->class('btn btn-sm btn-outline-success');
            $buttons[] = Button::add('force-delete')->slot('Hapus permanen')->dispatchSelf('prepare-row-action', ['action' => 'forceDelete', 'id' => $row->id])->class('btn btn-sm btn-outline-danger');
        }

        return $buttons;
    }

    public function prepareBulkAction(string $action): void
    {
        $this->prepareAction($action, $this->checkboxValues);
    }

    #[On('prepare-row-action')]
    public function prepareRowAction(string $action, int $id): void
    {
        $this->prepareAction($action, [$id]);
    }

    public function cancelPendingAction(): void
    {
        $this->pendingAction = null;
        $this->pendingIds = [];
    }

    public function executePendingAction(): void
    {
        $changed = match ($this->pendingAction) {
            'archive' => $this->users->archiveUsers($this->login(), $this->pendingIds),
            'restore' => $this->users->restoreUsers($this->login(), $this->pendingIds),
            'forceDelete' => $this->users->forceDeleteUsers($this->login(), $this->pendingIds),
            default => 0,
        };

        $this->checkboxValues = [];
        $this->cancelPendingAction();
        $this->dispatch('starter-toast', type: $changed > 0 ? 'success' : 'warning', message: $changed > 0 ? "{$changed} user berhasil diproses." : 'Tidak ada user yang dapat diproses.');
    }

    private function prepareAction(string $action, array $ids): void
    {
        abort_unless(in_array($action, ['archive', 'restore', 'forceDelete'], true), 422);

        $this->pendingIds = collect($ids)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();
        $this->pendingAction = $this->pendingIds === [] ? null : $action;
    }

    private function login(): ClientLogin
    {
        return $this->authenticatedLogins->settingsManager();
    }
}
