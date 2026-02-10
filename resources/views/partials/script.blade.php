<script data-exec-on-popstate>
    adminReady(function () {
        @foreach($script as $s){!! $s !!}@endforeach
    });
</script>