<div class="form-group row">
    <label class="col-md-{{$width['label']}} col-form-label">{{ $label }}</label>
    <div class="col-md-{{$width['field']}}">
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
            <div class="position-relative">
                @if($escape)
                    {{ $content }}
                @else
                    {!! $content !!}
                @endif
            </div>
        @endif
    </div>
</div>
