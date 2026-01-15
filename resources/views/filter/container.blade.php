<div class="card card-info {{ $expand ? '' : 'd-none' }} filter-card" id="{{ $filterID }}">
    <form action="{!! $action !!}" class="form-horizontal" pjax-container method="get">

        <div class="card-body">
            <div class="row">
                @foreach($layout->columns() as $column)
                    <div class="col-md-{{ $column->width() }}">
                        <div class="fields-group">
                            @foreach($column->filters() as $filter)
                                {!! $filter->render() !!}
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer">
            <div class="row">
                <div class="col-md-10 container">
                    <div class="d-flex">
                        <div class="btn-group mr-2">
                            <button class="btn btn-info btn-sm submit">
                                <i class="fas fa-search"></i>&nbsp;{{ trans('admin.search') }}
                            </button>
                        </div>
                        <div class="btn-group">
                            <a href="{!! $action !!}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-undo"></i>&nbsp;{{ trans('admin.reset') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
