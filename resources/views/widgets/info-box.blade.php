<div {!! $attributes !!}>
    <div class="inner">
        <h3>{{ $info }}</h3>

        <p>{{ $name }}</p>
    </div>
    <div class="icon">
        <i class="fas fa-{{ $icon }}"></i>
    </div>
    <a href="{{ $link }}" class="small-card-footer">
        {{ trans('admin.more') }}&nbsp;
        <i class="fas fa-arrow-circle-right"></i>
    </a>
</div>