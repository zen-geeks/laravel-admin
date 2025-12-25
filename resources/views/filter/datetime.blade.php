<div class="input-group">
    @if($group)
        <div class="input-group-append">
            <input type="hidden" name="{{ $id }}_group" class="{{ $group_name }}-operation" value="0"/>
            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" style="min-width: 32px;">
                <span class="{{ $group_name }}-label">{{ $default['label'] }}</span>
                &nbsp;&nbsp;
                <span class="fas fa-caret-down"></span>
            </button>
            <ul class="dropdown-menu {{ $group_name }}">
                @foreach($group as $index => $item)
                    <li><a class="dropdown-item" href="#" data-index="{{ $index }}"> {{ $item['label'] }} </a></li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="input-group-prepend">
        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
    </div>
    <input class="form-control datetimepicker-input" id="{{$id}}" placeholder="{{$label}}" name="{{$name}}" value="{{ request($name, $value) }}" data-toggle="datetimepicker" data-target="#{{$id}}">
</div>