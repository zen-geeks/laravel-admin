<div class="{{$viewClass['form-group']}}">
    <label class="{{$viewClass['label']}} col-form-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        <div class="card card-solid card-default no-margin">
            <!-- /.card-header -->
            <div class="card-header">
                {!! $value !!}&nbsp;
            </div><!-- /.card-body -->
        </div>

        @include('admin::form.help-block')

    </div>
</div>
