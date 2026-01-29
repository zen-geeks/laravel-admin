<!-- Main Footer -->
@php($show_footer = config('admin.show_footer'))
<footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-md-block">
        @if(config('admin.show_environment'))
            @if($show_footer)
                <strong>Env</strong>&nbsp;&nbsp;
            @endif
            {!! config('app.env') !!}
        @endif

        &nbsp;&nbsp;&nbsp;&nbsp;

        @if(config('admin.show_version'))
            @if($show_footer)
                <strong>Version</strong>&nbsp;&nbsp
            @endif
            {!! \Encore\Admin\Admin::VERSION !!}
        @endif
    </div>
    <!-- Default to the left -->
    @if(@$show_footer)
        <strong>Powered by <a href="https://github.com/z-song/laravel-admin" target="_blank">laravel-admin</a></strong>
    @else
        &nbsp;
    @endif
</footer>