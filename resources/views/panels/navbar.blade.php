@if($configData["mainLayoutType"] == 'horizontal')

    <nav class="header-navbar navbar-expand-lg navbar navbar-with-menu {{ $configData['navbarColor'] }} navbar-fixed">
        <div class="navbar-header d-xl-block d-none">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item"><a class="navbar-brand" href="">
                        <div class="brand-logo"></div>
                        {{--<div class="brand-logo-s"></div>--}}
{{--                        <h2 class="brand-text mb-0">DearTime</h2>--}}

                    </a></li>
            </ul>
        </div>
@else
    <nav class="header-navbar navbar-expand-lg navbar navbar-with-menu {{ $configData['navbarClass'] }} navbar-light navbar-shadow {{ $configData['navbarColor'] }}">
@endif
@php
    $guard = request()->routeIs('admin.*') ? 'internal_users':null;
@endphp

    <div class="navbar-wrapper">
        <div class="navbar-container content">
            <div class="navbar-collapse" id="navbar-mobile">
                <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">
                    <ul class="nav navbar-nav">
                        <li class="nav-item mobile-menu d-xl-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ficon feather icon-menu"></i></a></li>
                    </ul>
                </div>
                <ul class="nav navbar-nav float-right">

{{--                    <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand"><i class="ficon feather icon-maximize"></i></a></li>--}}
{{--                    <li class="nav-item nav-search"><a class="nav-link nav-link-search"><i class="ficon feather icon-search"></i></a>--}}
{{--                        <div class="search-input">--}}
{{--                            <div class="search-input-icon"><i class="feather icon-search primary"></i></div>--}}
{{--                            <input class="input" type="text" placeholder="Explore ..." tabindex="-1" data-search="starter-list" />--}}
{{--                            <div class="search-input-close"><i class="feather icon-x"></i></div>--}}
{{--                            <ul class="search-list"></ul>--}}
{{--                        </div>--}}
{{--                    </li>--}}
                    @auth
                        @if($guard != 'internal_users')
                            <li class="dropdown dropdown-language nav-item">
                                <a class="dropdown-toggle nav-link" id="dropdown-flag" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="selected-language">{{__('web/lang.'.app()->getLocale())}}</span>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdown-flag">
                                    <a class="dropdown-item langchg" href="?set_locale=en">{{__('web/lang.en')}}</a>
                                    <a class="dropdown-item langchg" href="?set_locale=bm">{{__('web/lang.bm')}}</a>
                                    <a class="dropdown-item langchg" href="?set_locale=ch">{{__('web/lang.ch')}}</a>
                                </div>
                            </li>
                        @endauth
                    @endauth
{{--                    <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand"><i class="ficon feather icon-maximize"></i></a></li>--}}
{{--                    <li class="nav-item nav-search"><a class="nav-link nav-link-search"><i class="ficon feather icon-search"></i></a>--}}
{{--                        <div class="search-input">--}}
{{--                            <div class="search-input-icon"><i class="feather icon-search primary"></i></div>--}}
{{--                            <input class="input" type="text" placeholder="Explore Vuesax..." tabindex="-1" data-search="template-list" />--}}
{{--                            <div class="search-input-close"><i class="feather icon-x"></i></div>--}}
{{--                            <ul class="search-list"></ul>--}}
{{--                        </div>--}}
{{--                    </li>--}}
                    @auth()
                        @if($guard != 'internal_users')
                        <li class="dropdown dropdown-notification nav-item"><a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon feather icon-bell"></i>
                                <span class="badge badge-pill badge-primary badge-up">{{\App\Notification::where("user_id",auth()->id())->where("is_read","0")->count()}}</span></a>
                            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                                <li class="dropdown-menu-header">
                                    <div class="dropdown-header m-0 p-2">
                                        <h3 class="white">{{\App\Notification::where("user_id",auth()->id())->where("is_read","0")->count()}}</h3><span class="grey darken-2">{{__('web/navbar.app_notifications')}}</span>
                                    </div>
                                </li>
                                <li class="scrollable-container media-list">
                                    @foreach(\App\Notification::where("user_id",auth()->id())->where("is_read","0")->orderBy("created_at","desc")->take(10)->get() as $notification)
                                        <a class="d-flex justify-content-between openPage" data-src="{{$notification->link}}?wb=1" href="#">
                                            <div class="media d-flex align-items-start">
                                                <div class="media-left"><i class="feather icon-monitor font-medium-5 primary"></i></div>
                                                <div class="media-body">
                                                    <h6 class="primary media-heading">{{$notification->title}}</h6><small class="notification-text">click to show details</small>
                                                </div><small>
                                                    <time class="media-meta">{{$notification->created_at->ago()}}</time></small>
                                            </div>
                                        </a>
                                    @endforeach

                                </li>
                                <li class="dropdown-menu-footer"><a class="dropdown-item p-1 text-center" href="{{route('userpanel.notification.index')}}">{{__('web/navbar.notification_area')}}</a></li>
                            </ul>
                        </li>
                        @endif
                    @endauth
                    <li class="dropdown dropdown-user nav-item">
                        <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                            <div class="user-nav d-sm-flex d-none">
                                <span class="user-name text-bold-600">
                                    @if(auth($guard)->check())
                                        {{auth($guard)->user()->name}}
                                    @else
                                        Guest
                                    @endif
                                </span>
                                <span class="user-status">{{__('web/navbar.available')}}</span>
                            </div>
                            <span>
                                <img class="round" src="{{auth($guard)->user()->selfie}}" alt="avatar" style="object-fit: cover;object-position: center;" height="40" width="40" />
                            </span>
                        </a>
                        @if(auth($guard)->check())
                            <div class="dropdown-menu dropdown-menu-right">
                                @if($guard == 'internal_users')
                                    <a class="dropdown-item" href="{{route('admin.ac.editprofile')}}"><i class="feather icon-user"></i>{{__('web/navbar.edit_profile')}}</a>
                                @else
                                     
                                    <a class="dropdown-item" href="{{route('userpanel.dashboard.profile')}}"><i class="feather icon-user"></i>{{__('web/navbar.edit_profile')}}</a>
                                    @if(auth($guard)->user()->enable_user_registration !=0)
                                    <a class="dropdown-item" href="{{route('userpanel.account.registration-form')}}"><i class="feather icon-user"></i>{{__('web/auth.register')}}</a>
                                    @endif  
                                    <a class="dropdown-item" href="{{route('userpanel.account.change-password')}}"><i class="feather icon-user"></i>{{__('web/navbar.change_password')}}</a>

                                @endif
                               
                                <div class="dropdown-divider"></div>
                                @if($guard == 'internal_users')
                                    <a class="dropdown-item" href="{{route('admin.auth.logout')}}"><i class="feather icon-power"></i> {{__('web/navbar.logout')}}</a>
                                @else
                                    <a class="dropdown-item" href="{{route('logout')}}"><i class="feather icon-power"></i> {{__('web/navbar.logout')}}</a>
                                @endif

                            </div>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<!-- END: Header-->
