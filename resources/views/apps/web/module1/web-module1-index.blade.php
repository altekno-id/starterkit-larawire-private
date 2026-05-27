<div>
    <div class="page-header d-print-none mb-3" aria-label="Header halaman">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $currentAppName }}</div>
                <h2 class="page-title">Data Module 1</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
                <h3 class="card-title">Data Module 1</h3>
        </div>
        <div class="card-body">
            <p class="text-secondary mb-3">Halaman default untuk route module ketika path berhenti di nama module.</p>
            <div class="rounded border bg-body-tertiary px-3 py-2 text-secondary">
                Active route: <strong>{{ $routeName }}</strong>
            </div>
        </div>
    </div>
</div>
