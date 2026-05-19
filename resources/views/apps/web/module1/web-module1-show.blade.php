<div>
    <div class="page-header d-print-none mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $currentAppName }}</div>
                <h2 class="page-title">Module 1 Show</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Module 1 Show</h3>
        </div>
        <div class="card-body">
            <p class="text-secondary mb-3">Halaman detail untuk Module 1.</p>
            @include('templates.components.alert', [
                'type' => 'info',
                'class' => 'mb-0',
                'message' => new \Illuminate\Support\HtmlString('Route aktif: <strong>'.e($routeName).'</strong>'.($id ? ' · ID: <strong>'.e($id).'</strong>' : '')),
            ])
        </div>
    </div>
</div>
