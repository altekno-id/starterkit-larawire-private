<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $pageTitle }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">{{ $currentAppName }}</li>
                        <li class="breadcrumb-item active">{{ $routeName }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Module 1 Index</h4>
            <p class="text-muted mb-2">Halaman default untuk route module ketika path berhenti di nama modul.</p>
            <div class="alert alert-info mb-0">
                Route aktif: <strong>{{ $routeName }}</strong>
            </div>
        </div>
    </div>
</div>
