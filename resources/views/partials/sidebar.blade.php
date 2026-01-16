<aside class="main-sidebar sidebar-light-primary elevation-4">

    <a href="{{ admin_url('/') }}" class="brand-link">
        <span class="logo-mini">{!! config('admin.logo-mini', config('admin.name')) !!}</span>
        {{--        <img src="{!! config('admin.logo-mini-path', '/vendor/laravel-admin/AdminLTE/dist/img/AdminLTELogo.png') !!}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">--}}
        <span class="brand-text font-weight-light">
            {!! config('admin.logo', config('admin.name')) !!}
        </span>
    </a>

    <div class="sidebar">

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ Admin::user()->avatar }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <p class="d-block">{{ Admin::user()->name }}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> {{ trans('admin.online') }}</a>
            </div>
        </div>

        @if(config('admin.enable_menu_search'))
            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar autocomplete" type="search" placeholder="Search..." autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
                <ul class="dropdown-menu" role="menu" style="min-width:210px;max-height:300px;overflow:auto;">
                    @foreach(Admin::menuLinks() as $link)
                        <li>
                            <a href="{{ admin_url($link['uri']) }}">
                                <i class="fas {{ $link['icon'] }}"></i>{{ admin_trans($link['title']) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @each('admin::partials.menu', Admin::menu(), 'item')
            </ul>
        </nav>

    </div>

</aside>
