@php($position = $position ?? 'bottom')

@if ($paginator->count() > 0)
    <div class="starter-pg-pagination d-flex flex-column flex-lg-row align-items-lg-center justify-content-end gap-2 text-end">
        @if ($recordCount === 'full')
            <small class="text-muted">Menampilkan <strong>{{ $paginator->firstItem() }}</strong> sampai <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> data</small>
        @elseif ($recordCount === 'short')
            <small class="text-muted"><strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong></small>
        @elseif ($recordCount === 'min')
            <small class="text-muted"><strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong></small>
        @endif

        @if ($paginator->hasPages())
            <nav aria-label="Navigasi halaman">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                        <button type="button" class="page-link" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled($paginator->onFirstPage()) aria-label="Halaman sebelumnya">&lsaquo;</button>
                    </li>
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                        @elseif (is_array($element))
                            @foreach ($element as $page => $url)
                                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}" wire:key="starter-pg-{{ $position }}-page-{{ $page }}">
                                    @if ($page == $paginator->currentPage())
                                        <span class="page-link" aria-current="page">{{ $page }}</span>
                                    @else
                                        <button type="button" class="page-link" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</button>
                                    @endif
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                    <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                        <button type="button" class="page-link" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages()) aria-label="Halaman berikutnya">&rsaquo;</button>
                    </li>
                </ul>
            </nav>
        @endif
    </div>
@endif
