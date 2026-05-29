@php
    $level = (int) ($level ?? 1);
    $menuId = 'sidebar-menu-'.substr(md5($menu['label'].$menu['url']), 0, 10);
    $isExpanded = (bool) ($menu['expanded'] ?? false);
    $isActive = (bool) ($menu['active'] ?? false);
    $icon = $menu['icon'] ?? 'circle';
@endphp

@if ($level === 1)
    <li class="nav-item {{ $isExpanded || $isActive ? 'active' : '' }}">
        @if ($menu['hasChildren'])
            <details class="starter-sidebar-details" @if ($isExpanded) open @endif>
                <summary class="nav-link cursor-pointer user-select-none" role="button" aria-controls="{{ $menuId }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        @include('templates.layouts.icon', ['name' => $icon])
                    </span>
                    <span class="nav-link-title">{{ $menu['label'] }}</span>
                </summary>
                <div class="dropdown-menu starter-sidebar-submenu position-static" id="{{ $menuId }}">
                    @foreach ($menu['children'] as $child)
                        @include('templates.layouts.menu-item', ['menu' => $child, 'level' => 2])
                    @endforeach
                </div>
            </details>
        @else
            <a href="{{ $menu['url'] }}" class="nav-link" @if ($isActive) data-current="true" @endif @if ($menu['url'] !== 'javascript:void(0);') data-starter-navigate data-starter-menu-url="{{ $menu['url'] }}" @endif>
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                    @include('templates.layouts.icon', ['name' => $icon])
                </span>
                <span class="nav-link-title">{{ $menu['label'] }}</span>
            </a>
        @endif
    </li>
@elseif ($menu['hasChildren'])
    <details class="starter-sidebar-details starter-sidebar-details-nested" @if ($isExpanded) open @endif>
        <summary class="dropdown-item cursor-pointer user-select-none {{ $isExpanded ? 'active' : '' }}" role="button" aria-controls="{{ $menuId }}">
            <span>{{ $menu['label'] }}</span>
        </summary>
        <div class="dropdown-menu starter-sidebar-submenu position-static" id="{{ $menuId }}">
            @foreach ($menu['children'] as $child)
                @include('templates.layouts.menu-item', ['menu' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    </details>
@else
    <a href="{{ $menu['url'] }}" class="dropdown-item {{ $isActive ? 'active' : '' }}" @if ($isActive) data-current="true" @endif @if ($menu['url'] !== 'javascript:void(0);') data-starter-navigate data-starter-menu-url="{{ $menu['url'] }}" @endif>
        {{ $menu['label'] }}
    </a>
@endif
