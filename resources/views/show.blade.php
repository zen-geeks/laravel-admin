<div class="row">
    <div class="col-lg-12">
        {!! $panel !!}
    </div>

    <div class="col-lg-12">
        @foreach($relations as $relation)
            {!!  $relation->render() !!}
        @endforeach
    </div>
</div>