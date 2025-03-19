@php
    use App\Helpers;
    $configData = Helpers::applClasses();
@endphp
{{-- Horizontal Menu --}}
<div class="horizontal-menu-wrapper">
    <div class="header-navbar navbar-expand-sm navbar navbar-horizontal floating-nav {{($configData['theme'] === 'light') ? "navbar-light" : "navbar-dark" }} navbar-without-dd-arrow navbar-shadow navbar-brand-center" role="navigation" data-menu="menu-wrapper" data-nav="brand-center">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mr-auto">
                    <a class="navbar-brand" href="{{url('')}}">
                        <div class="brand-logo"></div>
                        <h2 class="brand-text mb-0"></h2>
                    </a>
                </li>
                <li class="nav-item nav-toggle">
                    <a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse">
                        <i class="feather icon-x d-block d-xl-none font-medium-4 primary toggle-icon"></i>
                        <i class="toggle-icon feather icon-disc font-medium-4 d-none d-xl-block collapse-toggle-icon primary" data-ticon="icon-disc"></i>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Horizontal menu content-->
        <div class="navbar-container main-menu-content" data-menu="menu-container">
            <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
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
                    @php
                        $custom_classes = "";
                        if(isset($menu->classlist)) {
                            $custom_classes = $menu->classlist;
                        }
                        $translation = "";
                        if(isset($menu->i18n)){
                            $translation = $menu->i18n;
                        }
                    @endphp
                    <li class="dropdown nav-item {{ (!isset($menu->submenu)  && request()->routeIs($menu->route)) ? 'active' : '' }} {{ $custom_classes }}" @if(isset($menu->submenu))  data-menu="dropdown" @endif>
                        <a href="{{ url($menu->url) }}" @if(isset($menu->submenu)) class="dropdown-toggle nav-link" data-toggle="dropdown" @endif>
                            <i class="{{ $menu->icon }}"></i>
                            <span>{{ __('web/menu.'.$menu->name) }}</span>
                        </a>
                        @if(isset($menu->submenu))
                            @include('panels/horizontalSubmenu', ['menu' => $menu->submenu])
                        @endif
                    </li>
                @endforeach
                {{-- Foreach menu item ends --}}
            </ul>
        </div>
    </div>
</div>
