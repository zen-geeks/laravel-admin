@extends('admin::index', ['header' => strip_tags($header)])

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        {!! $header ?: trans('admin.title') !!}
                        @if($description)
                            <small class="text-muted">{{ $description }}</small>
                        @endif
                    </h1>
                </div>

                <div class="col-sm-6">
                    @if ($breadcrumb)
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="{{ admin_url('/') }}">
                                    <i class="fas fa-home"></i> {{ __('Home') }}
                                </a>
                            </li>
                            @foreach($breadcrumb as $item)
                                @if($loop->last)
                                    <li class="breadcrumb-item active">
                                        @if (\Illuminate\Support\Arr::has($item, 'icon'))
                                            <i class="fas fa-{{ $item['icon'] }}"></i>
                                        @endif
                                        {{ $item['text'] }}
                                    </li>
                                @else
                                    <li class="breadcrumb-item">
                                        @if (\Illuminate\Support\Arr::has($item, 'url'))
                                            <a href="{{ admin_url(\Illuminate\Support\Arr::get($item, 'url')) }}">
                                                @if (\Illuminate\Support\Arr::has($item, 'icon'))
                                                    <i class="fas fa-{{ $item['icon'] }}"></i>
                                                @endif
                                                {{ $item['text'] }}
                                            </a>
                                        @else
                                            @if (\Illuminate\Support\Arr::has($item, 'icon'))
                                                <i class="fas fa-{{ $item['icon'] }}"></i>
                                            @endif
                                            {{ $item['text'] }}
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    @elseif(config('admin.enable_default_breadcrumb'))
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="{{ admin_url('/') }}">
                                    <i class="fas fa-home"></i> {{ __('Home') }}
                                </a>
                            </li>
                            @for($i = 2; $i <= count(Request::segments()); $i++)
                                <li class="breadcrumb-item">
                                    {{ ucfirst(Request::segment($i)) }}
                                </li>
                            @endfor
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            @include('admin::partials.alerts')
            @include('admin::partials.exception')
            @include('admin::partials.toastr')

            @if($_view_)
                @include($_view_['view'], $_view_['data'])
            @else
                {!! $_content_ !!}
            @endif

        </div>
    </section>
@endsection
