@inject('menuItemHelper', 'JeroenNoten\LaravelAdminLte\Helpers\MenuItemHelper')

@if ($menuItemHelper->isHeader($item))

    {{-- Header --}}
    @include('adminlte::partials.sidebar.menu-item-header')



@elseif ($menuItemHelper->isLink($item))

    {{-- Link --}}
    @include('adminlte::partials.sidebar.menu-item-link')

@endif
