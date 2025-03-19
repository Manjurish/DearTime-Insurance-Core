{{-- For Horizontal submenu --}}
<ul class="dropdown-menu">
    @foreach($menu as $submenu)
        <?php     
        $route = $submenu->route;
        //check if has permission
        $route = new \Mmeshkatian\Ariel\ActionContainer($route,'');
        $url = str_replace(url()->to('/')."/","",$route->getUrl());
        $submenu->url = $url.($submenu->extra ?? '');

        ?>
        @continue((!\App\Helpers::hasPermission($route->action)))
        <?php     
        $custom_classes = "";
        if(isset($submenu->classlist)) {
            $custom_classes = $submenu->classlist;
        }
        $submenuTranslation = "";
        if(isset($menu->i18n)){
            $submenuTranslation = $menu->i18n;
        }
        ?>
        <li class="{{ (request()->routeIs($submenu->route)) ? 'active' : '' }} {{ (isset($submenu->submenu)) ? "dropdown dropdown-submenu" : '' }} {{ $custom_classes }}">
            <a href="{{ asset($submenu->url) }}" class="dropdown-item {{ (isset($submenu->submenu)) ? "dropdown-toggle" : '' }}" {{ (isset($submenu->submenu)) ? 'data-toggle=dropdown' : '' }}>
                <i class="{{ isset($submenu->icon) ? $submenu->icon : "" }}"></i>
                <span data-i18n="{{ $submenuTranslation }}">{{ __('web/menu.'.$submenu->name) }}</span>
            </a>
            @if (isset($submenu->submenu))
                @include('panels/horizontalSubmenu', ['menu' => $submenu->submenu])
            @endif
        </li>
    @endforeach
</ul>
