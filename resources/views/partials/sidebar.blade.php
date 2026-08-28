<aside class="app-sidebar bg-body elevation-4 shadow" data-bs-theme="light">

    <div class="sidebar-brand pe-2 ps-2">
        <a href="{{ admin_url('/', absolute: false) }}" class="brand-link">
            <span class="logo-mini">{!! config('admin.logo-mini', config('admin.name')) !!}</span>
            {{--        <img src="{!! config('admin.logo-mini-path', '/vendor/laravel-admin/AdminLTE/dist/img/AdminLTELogo.png') !!}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">--}}
            <span class="brand-text fw-light">
                {!! config('admin.logo', config('admin.name')) !!}
            </span>
        </a>
    </div>

    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="{{ Admin::user()->avatar }}" class="user-image rounded-circle shadow" alt="User Image">
        </div>
        <div class="info">
            <p class="d-block">{{ Admin::user()->name }}</p>
            <!-- Status -->
            <a href="#"><i class="fa fa-circle text-success"></i> {{ trans('admin.online') }}</a>
        </div>
    </div>

    @if(config('admin.enable_menu_search'))
        <div class="form-inline pe-2 ps-2">
            <div class="input-group" data-widget="sidebar-search" data-highlight-class="text-primary">
                <input class="form-control form-control-sidebar autocomplete" type="search" placeholder="Search..." autocomplete="off" aria-describedby="sidebar-search" data-lte-toggle="sidebar-search">
                <button class="btn btn-outline-secondary" type="button" id="sidebar-search">
                    <i class="fas fa-search fa-fw"></i>
                </button>
            </div>

            <ul class="dropdown-menu" role="menu" style="min-width:210px;max-height:300px;overflow:auto;">@foreach(Admin::menuLinks() as $link)<li><a href="{{ admin_url($link['uri'], absolute: false) }}"><i class="fas {{ $link['icon'] }}"></i>{{ admin_trans($link['title']) }}</a></li>@endforeach</ul>
        </div>
    @endif

    <div class="sidebar-wrapper pe-2 ps-2">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="true" id="navigation">@each('admin::partials.menu', Admin::menu(), 'item')</ul>
        </nav>
    </div>
</aside>
