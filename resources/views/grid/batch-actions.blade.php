@if(!$holdAll)
<div class="btn-group {{ $all }}-btn" style="display:none;margin-right: 5px;">
    <a class="btn btn-sm btn-secondary d-none d-md-inline"><span class="selected"></span></a>
    @if(!$actions->isEmpty())
        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu" role="menu">
            @foreach($actions as $action)
                @if($action instanceof \Encore\Admin\Actions\BatchAction)
                    <li class="dropdown-item">{!! $action->render() !!}</li>
                @else
                    <li class="dropdown-item"><a href="#" class="{{ $action->getElementClass(false) }}">{!! $action->render() !!} </a></li>
                @endif
            @endforeach
        </ul>
    @endif
</div>
@endif

<script>
    $('.{{ $all }}').on('change', function () {

        if (this.checked) {
            $('.{{ $row }}-checkbox')
                .prop('checked', true)
                .trigger('change');
        } else {
            $('.{{ $row }}-checkbox')
                .prop('checked', false)
                .trigger('change');
        }

    }).on('click', function () {

        if (this.checked) {
            $.admin.grid.selects = {};
        } else {
            $('.{{ $row }}-checkbox').each(function () {
                var id = $(this).data('id');
                $.admin.grid.select(id);
            });
        }

        var selected = $.admin.grid.selected().length;

        $('.{{ $all }}-btn').toggle(selected > 0);

        $('.{{ $all }}-btn .selected')
            .html("{{ trans('admin.grid_items_selected') }}".replace('{n}', selected));
    });
</script>

