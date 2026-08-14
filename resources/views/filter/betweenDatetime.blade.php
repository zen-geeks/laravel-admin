<div class="form-group row">
    <label class="col-12 col-md-2 col-form-label">{{$label}}</label>
    <div class="col-12 col-md-8">
        <div class="input-group">
            <span class="input-group-text"><i class="far fa-calendar"></i></span>
            <input type="text"
                   class="form-control datetimepicker-input"
                   id="{{$id['start']}}"
                   placeholder="{{$label}}"
                   name="{{$name['start']}}"
                   value="{{ request()->input("{$column}.start", \Illuminate\Support\Arr::get($value, 'start')) }}"
                   autocomplete="off"
                   data-bs-toggle="datetimepicker"
                   data-bs-target="#{{$id['start']}}"
            />

            <span class="input-group-text">-</span>

            <input type="text"
                   class="form-control datetimepicker-input"
                   id="{{$id['end']}}"
                   placeholder="{{$label}}"
                   name="{{$name['end']}}"
                   value="{{ request()->input("{$column}.end", \Illuminate\Support\Arr::get($value, 'end')) }}"
                   autocomplete="off"
                   data-bs-toggle="datetimepicker"
                   data-bs-target="#{{$id['end']}}"
            />
        </div>
    </div>
</div>
