<div class="form-group">
    <label class="col-2 col-form-label">{{$label}}</label>
    <div>
        <div class="input-group input-group-sm">
            <input type="text" class="form-control {{$id['start']}}" placeholder="{{$label}}" name="{{$name['start']}}" value="{{ request()->input("{$column}.start", \Illuminate\Support\Arr::get($value, 'start')) }}">
            <div class="input-group-prepend" style="border-left: 0; border-right: 0;"><span class="input-group-text">-</span></div>
            <input type="text" class="form-control {{$id['end']}}" placeholder="{{$label}}" name="{{$name['end']}}" value="{{ request()->input("{$column}.end", \Illuminate\Support\Arr::get($value, 'end')) }}">
        </div>
    </div>
</div>