<div class="input-group input-group-sm">
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
            <span class="input-group-text"><i class="fas fa-{{ $icon }}"></i></span>
        </div>

    <input type="{{ $type }}" class="form-control {{ $id }}" placeholder="{{$placeholder}}" name="{{$name}}" value="{{ request($name, $value) }}">
</div>