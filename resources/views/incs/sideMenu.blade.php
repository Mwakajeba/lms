<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <!-- <img src="{{ asset('assets/images/logo1.png') }}" width="120" style="color:#fff" alt="logo icon">  -->
            <h5 style="color:#fff">SMARTFINANCE</h5> 
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-first-page'></i></div>
    </div>

    <!--navigation-->
    <ul class="metismenu" id="menu">
        @foreach($menus as $menu)
            @php
                $isEditOrDelete = Str::contains($menu->route, ['edit', 'delete','destroy', 'create']);
            @endphp

            @if($menu->children->count())
                <li>
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="{{ $menu->icon ?? 'bx bx-folder' }}"></i></div>
                        <div class="menu-title">{{ $menu->name }}</div>
                    </a>
                    <ul>
                        @foreach($menu->children as $child)
                            @php
                                $isChildEditOrDelete = Str::contains($child->route, ['edit', 'delete', 'destroy', 'create']);
                            @endphp

                            @if (!$isChildEditOrDelete && $child->route)
                                @auth
                                @if (Route::has($child->route))
                                <li>
                                    <a href="{{ route($child->route) }}">
                                        <i class="bx bx-right-arrow-alt"></i>{{ $child->name }}
                                    </a>
                                </li>
                                @endif
                                @endauth
                            @endif
                        @endforeach
                    </ul>
                </li>
            @elseif(!$isEditOrDelete && $menu->route)
                @auth
                @if (Route::has($menu->route))
                <li>
                    <a href="{{ route($menu->route) }}">
                        <div class="parent-icon"><i class="{{ $menu->icon ?? 'bx bx-circle' }}"></i></div>
                        <div class="menu-title">{{ $menu->name }}</div>
                    </a>
                </li>
                @endif
                @endauth
            @endif
        @endforeach
    </ul>
    <!--end navigation-->
</div>
