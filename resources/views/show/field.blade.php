<div class="form-group row">
    <label class="col-{{$width['label']}} col-form-label">{{ $label }}</label>
    <div class="col-{{$width['field']}}">
        @if($wrapped)
        <div class="card card-solid card-default no-margin card-show">
            <!-- /.card-header -->
            <div class="card-body">
                @if($escape)
                    {{ $content }}&nbsp;
                @else
                    {!! $content !!}&nbsp;
                @endif
            </div><!-- /.card-body -->
        </div>
        @else
            @if($escape)
                {{ $content }}
            @else
                {!! $content !!}
            @endif
        @endif
    </div>
</div>
