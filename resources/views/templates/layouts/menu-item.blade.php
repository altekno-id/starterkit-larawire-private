<li>
    @if ($menu['hasChildren'])
        <a href="javascript:void(0);" class="has-arrow waves-effect">
            @if ($menu['icon'])
                <i class="{{ $menu['icon'] }}"></i>
            @endif
            <span>{{ $menu['label'] }}</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            @foreach ($menu['children'] as $child)
                @include('templates.layouts.menu-item', ['menu' => $child])
            @endforeach
        </ul>
    @else
        <a href="{{ $menu['url'] }}" class="waves-effect" @if ($menu['url'] !== 'javascript:void(0);') wire:navigate @endif>
            @if ($menu['icon'])
                <i class="{{ $menu['icon'] }}"></i>
            @endif
            <span>{{ $menu['label'] }}</span>
        </a>
    @endif
</li>
