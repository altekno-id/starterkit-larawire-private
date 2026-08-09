@php($position = $position ?? 'bottom')

@if ($paginator->count() > 0)
    <div class="starter-pg-pagination" wire:loading.class="opacity-50">
        @if ($recordCount === 'full')
            <div class="starter-pg-record-count">
                Menampilkan <strong>{{ $paginator->firstItem() }}</strong> sampai <strong>{{ $paginator->lastItem() }}</strong>
                dari <strong>{{ $paginator->total() }}</strong> data
            </div>
        @elseif ($recordCount === 'short')
            <div class="starter-pg-record-count"><strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong></div>
        @elseif ($recordCount === 'min')
            <div class="starter-pg-record-count"><strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong></div>
        @endif

        @if ($paginator->hasPages())
            <nav aria-label="Navigasi halaman">
                <div class="starter-pg-pages">
                    <button type="button" class="starter-pg-page" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled($paginator->onFirstPage()) aria-label="Halaman sebelumnya">&lsaquo;</button>
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="starter-pg-page is-disabled">{{ $element }}</span>
                        @elseif (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="starter-pg-page is-active" wire:key="starter-pg-{{ $position }}-page-{{ $page }}" aria-current="page">{{ $page }}</span>
                                @else
                                    <button type="button" class="starter-pg-page" wire:key="starter-pg-{{ $position }}-page-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    <button type="button" class="starter-pg-page" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages()) aria-label="Halaman berikutnya">&rsaquo;</button>
                </div>
            </nav>
        @endif
    </div>
@endif
