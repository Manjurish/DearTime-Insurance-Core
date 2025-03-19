<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto">
                @auth('internal_users')
                    <a class="navbar-brand" href="{{route('admin.dashboard.main')}}">
                        <div class="brand-logo"></div>
{{--
                        <div class="brand-logo-s"></div>
--}}
{{--                        <h2 class="brand-text mb-0">DearTime</h2>--}}
                    </a>
                @endif
                @auth()
                    <a class="navbar-brand" href="{{route('userpanel.dashboard.main')}}">
                        <div class="brand-logo"></div>
{{--
                        <div class="brand-logo-s"></div>
--}}
                        <h2 class="brand-text mb-0"></h2>
                    </a>
                @endif
            </li>
            <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="feather icon-x d-block d-xl-none font-medium-4 primary toggle-icon"></i><i class="toggle-icon feather icon-disc font-medium-4 d-none d-xl-block primary" data-ticon="icon-disc"></i></a></li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            {{-- Foreach menu item starts --}}
            @foreach(\App\Helpers::getMenuContents()->menu as $menu)
                <?php     
                    $route = $menu->route;
                    //check if has permission
                    $route = new \Mmeshkatian\Ariel\ActionContainer($route,'');
                    $url = str_replace(url()->to('/')."/","",$route->getUrl());
                    $menu->url = $url.($menu->extra ?? '');

                ?>
                @continue((!\App\Helpers::hasPermission($route->action)))

                <li class="nav-item {{ (!isset($menu->submenu)  && request()->routeIs($menu->route)) ? 'active' : '' }}">
                    <a href="{{ url($menu->url) }}">
                        <i class="{{ $menu->icon }}"></i>
                        <span class="menu-title" data-i18n="">{{ ($menu->name) }}</span>
                        @if (isset($menu->badge))
                            <?php      $badgeClasses = "badge badge-pill badge-primary float-right" ?>
                            <span class="{{ isset($menu->badgeClass) ? $menu->badgeClass.' test' : $badgeClasses.' notTest' }} ">{{$menu->badge}}</span>
                        @endif
                    </a>
                    @if(isset($menu->submenu))
                        @include('panels/submenu', ['menu' => $menu->submenu])
                    @endif
                </li>
            @endforeach
            {{-- Foreach menu item ends --}}
        </ul>
    </div>
</div>
<!-- END: Main Menu-->
