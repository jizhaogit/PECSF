<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
    
        <div class="image text-center">
            {{-- added by James Poon on 2022-Jan-28 --}}
            @if( session('profilePhoto') )
                <span>
                    <img style="width: 90px; height: 90px;" class="img-circle elevation-2 mt-4" src="data:image/jpeg;base64,{{ session('profilePhoto') }}" alt="">
                </span>
            @else
                <span>
                    <img src="{{asset('img/profile-pic.png')}}" style="max-width:90px; max-height:90px" class="img-circle elevation-2 mt-4">
                </span>
            @endif
        </div>
        <div class="info text-center mt-4 mb-5">
        <h5 class="text-light">Welcome back,</h5>
          <h4  style="color:#4C81AF !important;">{{ Auth::user()->name }}</h4>
        </div>
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

</aside>