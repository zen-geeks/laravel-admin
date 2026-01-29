<div class="btn-group" style="margin-right: 5px">
    <button class="btn btn-primary btn-sm btn-dropbox {{ $btn_class }} {{ $expand ? 'active' : '' }}" title="{{ trans('admin.filter') }}">
        <input type="checkbox" class="d-none "><i class="fas fa-filter"></i><span class="d-none d-md-inline">&nbsp;&nbsp;{{ trans('admin.filter') }}</span>
    </button>


    @if($scopes->isNotEmpty())
    <button type="button" class="btn btn-sm btn-dropbox dropdown-toggle btn-primary" data-toggle="dropdown">

        <span>{{ $label }}</span>
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu" role="menu">
        @foreach($scopes as $scope)
            {!! $scope->render() !!}
        @endforeach
        <li role="separator" class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ $cancel }}">{{ trans('admin.cancel') }}</a></li>
    </ul>
    @endif
</div>

<script>
var $btn = $('.{{ $btn_class }}');
var $filter = $('#{{ $filter_id }}');

$btn.unbind('click').click(function (e) {
    if ($filter.is(':visible')) {
        $filter.addClass('d-none');
    } else {
        $filter.removeClass('d-none');
    }
});
</script>
