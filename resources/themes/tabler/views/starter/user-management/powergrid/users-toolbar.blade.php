<div class="card-body border-bottom">
    <div class="row g-2 align-items-center">
        <div class="col-sm-4">
            <select class="form-select" wire:model.live="archiveStatus" aria-label="Status arsip user">
                <option value="active">Data aktif</option>
                <option value="archived">Arsip</option>
                <option value="all">Semua data</option>
            </select>
        </div>
        <div class="col-sm-8">
            <div class="btn-list justify-content-sm-end">
                @if ($archiveStatus !== 'archived')
                    <button type="button" class="btn btn-outline-warning" wire:click="prepareBulkAction('archive')" @disabled($checkboxValues === [])>Arsipkan terpilih</button>
                @endif
                @if ($archiveStatus !== 'active')
                    <button type="button" class="btn btn-outline-success" wire:click="prepareBulkAction('restore')" @disabled($checkboxValues === [])>Pulihkan terpilih</button>
                    <button type="button" class="btn btn-outline-danger" wire:click="prepareBulkAction('forceDelete')" @disabled($checkboxValues === [])>Hapus permanen</button>
                @endif
            </div>
        </div>
    </div>
</div>

@include('starter.templates.components.danger-modal', [
    'id' => 'users-lifecycle-modal',
    'title' => $pendingAction === 'forceDelete' ? 'Hapus user permanen?' : ($pendingAction === 'restore' ? 'Pulihkan user?' : 'Arsipkan user?'),
    'message' => $pendingAction === 'forceDelete'
        ? 'Data yang dihapus permanen tidak dapat dipulihkan.'
        : count($pendingIds).' user akan diproses.',
    'confirmText' => $pendingAction === 'forceDelete' ? 'Hapus Permanen' : ($pendingAction === 'restore' ? 'Pulihkan' : 'Arsipkan'),
    'confirmAction' => 'executePendingAction',
    'cancelAction' => 'cancelPendingAction',
    'visible' => $pendingAction !== null,
    'dismissOnConfirm' => false,
])
@if ($pendingAction !== null)
    <div class="modal-backdrop fade show"></div>
@endif
