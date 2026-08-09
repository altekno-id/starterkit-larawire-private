@php($position = $position ?? 'bottom')

<footer id="pg-footer-{{ $position }}" class="{{ theme_style($theme, 'footer.footer') }} starter-pg-footer-{{ $position }}">
    @if (filled(data_get($setUp, 'footer.perPage')) && count(data_get($setUp, 'footer.perPageValues')) > 1)
        <label class="starter-pg-per-page d-flex align-items-center gap-2">
            <select wire:model.live="setUp.footer.perPage" class="form-select {{ theme_style($theme, 'footer.select') }}">
                @foreach (data_get($setUp, 'footer.perPageValues') as $value)
                    <option value="{{ $value }}">{{ $value == 0 ? 'Semua' : $value }}</option>
                @endforeach
            </select>
            <small class="text-muted text-nowrap">Data per halaman</small>
        </label>
    @endif

    @if (method_exists($this->records, 'links'))
        {!! $this->records->links('starter.powergrid.pagination', [
            'recordCount' => data_get($setUp, 'footer.recordCount'),
            'position' => $position,
        ]) !!}
    @endif
</footer>
