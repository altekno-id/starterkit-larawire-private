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
            <h4 class="card-title">Module 1 Edit</h4>
            <p class="text-muted mb-2">Halaman form edit untuk Module 1.</p>
            <div class="alert alert-info mb-0">
                Route aktif: <strong>{{ $routeName }}</strong>
                @if ($id)
                    · ID: <strong>{{ $id }}</strong>
                @endif
            </div>
        </div>
    </div>
</div>
