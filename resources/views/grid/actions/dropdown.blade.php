<div class="grid-dropdown-actions dropdown">
    <a href="#" style="padding: 0 10px;" class="dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></a>
    <ul class="dropdown-menu actions-dropdown-menu">
@foreach($default as $action)<li class="dropdown-item">{!! $action->render() !!}</li>@endforeach
@if(!empty($custom))
    @if(!empty($default))<li class="dropdown-divider"></li>@endif
    @foreach($custom as $action)<li class="dropdown-item">{!! $action->render() !!}</li>@endforeach
@endif
    </ul>
</div>

@yield('child')
