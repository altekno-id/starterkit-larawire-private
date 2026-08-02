<div class="card-body border-bottom">
    <div class="row g-2 align-items-center">
        <div class="col-sm-4">
            <select class="form-select" wire:model.live="archiveStatus" aria-label="Status arsip role">
                <option value="active">Data aktif</option><option value="archived">Arsip</option><option value="all">Semua data</option>
            </select>
        </div>
        <div class="col-sm-8"><div class="btn-list justify-content-sm-end">
            @if ($archiveStatus !== 'archived')<button type="button" class="btn btn-outline-warning" wire:click="prepareBulkAction('archive')" @disabled($checkboxValues === [])>Arsipkan terpilih</button>@endif
            @if ($archiveStatus !== 'active')
                <button type="button" class="btn btn-outline-success" wire:click="prepareBulkAction('restore')" @disabled($checkboxValues === [])>Pulihkan terpilih</button>
                <button type="button" class="btn btn-outline-danger" wire:click="prepareBulkAction('forceDelete')" @disabled($checkboxValues === [])>Hapus permanen</button>
            @endif
        </div></div>
    </div>
</div>
@include('starter.templates.components.danger-modal', [
    'id' => 'roles-lifecycle-modal',
    'title' => $pendingAction === 'forceDelete' ? 'Hapus role permanen?' : ($pendingAction === 'restore' ? 'Pulihkan role?' : 'Arsipkan role?'),
    'message' => $pendingAction === 'forceDelete' ? 'Data dan relasi akses role akan dihapus permanen.' : count($pendingIds).' role akan diproses. Role yang masih digunakan tidak akan diubah.',
    'confirmText' => $pendingAction === 'forceDelete' ? 'Hapus Permanen' : ($pendingAction === 'restore' ? 'Pulihkan' : 'Arsipkan'),
    'confirmAction' => 'executePendingAction', 'cancelAction' => 'cancelPendingAction',
    'visible' => $pendingAction !== null, 'dismissOnConfirm' => false,
])
@if ($pendingAction !== null)<div class="modal-backdrop fade show"></div>@endif
