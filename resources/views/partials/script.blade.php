<script data-exec-on-popstate>
    (function () {
        const __adminInlineInit = function () {
            @foreach($script as $s){!! $s !!}@endforeach
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', __adminInlineInit, {once: true});
        } else {
            __adminInlineInit();
        }

    })();
</script>
