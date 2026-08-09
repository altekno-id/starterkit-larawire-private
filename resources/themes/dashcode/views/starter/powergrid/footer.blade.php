@php($position = $position ?? 'bottom')

<footer id="pg-footer-{{ $position }}" class="{{ theme_style($theme, 'footer.footer') }} starter-pg-footer-{{ $position }}">
    <div class="{{ theme_style($theme, 'footer.footer_with_pagination') }}">
        @if (filled(data_get($setUp, 'footer.perPage')) && count(data_get($setUp, 'footer.perPageValues')) > 1)
            <label class="starter-pg-per-page">
                <span>Data per halaman</span>
                <span class="relative">
                    <select wire:model.live="setUp.footer.perPage" class="{{ theme_style($theme, 'footer.select') }}">
                        @foreach (data_get($setUp, 'footer.perPageValues') as $value)
                            <option value="{{ $value }}">{{ $value == 0 ? 'Semua' : $value }}</option>
                        @endforeach
                    </select>
                    <span class="starter-pg-select-icon" aria-hidden="true">
                        <x-livewire-powergrid::icons.down class="w-4 h-4" />
                    </span>
                </span>
            </label>
        @endif

        @if (method_exists($this->records, 'links'))
            {!! $this->records->links('starter.powergrid.pagination', [
                'recordCount' => data_get($setUp, 'footer.recordCount'),
                'position' => $position,
            ]) !!}
        @endif
    </div>
</footer>
