<body class="vertical-layout vertical-menu-modern 2-columns {{ $configData['blankPageClass'] }} {{ $configData['bodyClass'] }}  {{($configData['theme'] === 'light') ? '' : $configData['theme'] }}  {{ $configData['navbarType'] }} {{ $configData['sidebarClass'] }} {{ $configData['footerType'] }}" data-menu="vertical-menu-modern" data-col="2-columns"  data-layout="{{ $configData['theme'] }}">
@include('panels.loading')
{{-- Include Sidebar --}}
@include('panels.sidebar')

<!-- BEGIN: Content-->
<div class="app-content content">
    <!-- BEGIN: Header-->
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    {{-- Include Navbar --}}
    @include('panels.navbar')

    @if(!empty($configData['contentLayout']))
        <div class="content-area-wrapper">
            <div class="{{ $configData['sidebarPositionClass'] }}">
                <div class="sidebar">
                    {{-- Include Sidebar Content --}}
                    @yield('content-sidebar')
                </div>
            </div>
            <div class="{{ $configData['contentsidebarClass'] }}">
                <div class="content-wrapper">
                    <div class="content-body">
                        @include('ariel::alerts')
                        {{-- Include Page Content --}}
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="content-wrapper">
            {{-- Include Breadcrumb --}}
            @if($configData['pageHeader'] == true)
                @include('panels.breadcrumb')
            @endif

            <div class="content-body">
                {{-- Include Page Content --}}
                @yield('content')
            </div>
        </div>
    @endif

</div>
<!-- End: Content-->


<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

{{-- include footer --}}
@include('panels/footer')

{{-- include default scripts --}}
@include('panels/scripts')
@include('panels.swal_msg')

{{-- Include page script --}}
@yield('myscript')
@livewireScripts
</body>
</html>
