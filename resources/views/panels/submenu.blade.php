{{-- For submenu --}}
<ul class="menu-content">
    @foreach($menu as $submenu)
        <?php     
        $route = $submenu->route;
        //check if has permission
        $route = new \Mmeshkatian\Ariel\ActionContainer($route,'');
        $url = str_replace(url()->to('/')."/","",$route->getUrl());
        $submenu->url = $url.($submenu->extra ?? '');

        ?>
        @continue((!\App\Helpers::hasPermission($route->action)))
        <li class="{{ (request()->is($submenu->url.'*')) ? 'active' : '' }}">
            <a href="{{ url($submenu->url) }}">
                <i class="{{ isset($submenu->icon) ? $submenu->icon : "" }}"></i>
                <span class="menu-title" data-i18n="">{{ ($submenu->name) }}</span>
            </a>
            @if (isset($submenu->submenu))
                @include('panels/submenu', ['menu' => $submenu->submenu])
            @endif
        </li>
    @endforeach
</ul>
