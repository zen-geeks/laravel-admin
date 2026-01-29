<div class="row">
    @foreach($fields as $field)
    <div class="col-lg-{{ $field['width'] }}">
        {!! $field['element']->render() !!}
    </div>
    @endforeach
</div>