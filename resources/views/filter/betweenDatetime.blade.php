<div class="form-group row">
    <label class="col-12 col-md-2 col-form-label">{{$label}}</label>
    <div class="col-12 col-md-8">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="far fa-calendar"></i></span>
            </div>
            <input type="text"
                   class="form-control datetimepicker-input"
                   id="{{$id['start']}}"
                   placeholder="{{$label}}"
                   name="{{$name['start']}}"
                   value="{{ request()->input("{$column}.start", \Illuminate\Support\Arr::get($value, 'start')) }}"
                   autocomplete="off"
                   data-toggle="datetimepicker"
                   data-target="#{{$id['start']}}"
            />

            <div class="input-group-prepend" style="border-left: 0; border-right: 0;"><span class="input-group-text">-</span></div>

            <input type="text"
                   class="form-control datetimepicker-input"
                   id="{{$id['end']}}"
                   placeholder="{{$label}}"
                   name="{{$name['end']}}"
                   value="{{ request()->input("{$column}.end", \Illuminate\Support\Arr::get($value, 'end')) }}"
                   autocomplete="off"
                   data-toggle="datetimepicker"
                   data-target="#{{$id['end']}}"
            />
        </div>
    </div>
</div>
