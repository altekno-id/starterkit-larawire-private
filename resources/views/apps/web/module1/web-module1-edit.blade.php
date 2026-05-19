<div>
    <div class="page-header d-print-none mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $currentAppName }}</div>
                <h2 class="page-title">Module 1 Edit</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Module 1 Edit</h3>
        </div>
        <div class="card-body">
            <p class="text-secondary mb-3">Halaman form edit untuk Module 1.</p>
            <div class="alert alert-info mb-0">
                Route aktif: <strong>{{ $routeName }}</strong>
                @if ($id)
                    · ID: <strong>{{ $id }}</strong>
                @endif
            </div>
        </div>
    </div>
</div>
