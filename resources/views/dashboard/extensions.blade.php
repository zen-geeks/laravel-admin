<style>
    .ext-icon {
        width: 42px;
        flex: 0 0 42px;
        text-align: center;
        color: rgba(var(--bs-body-color-rgb), .5);
    }

    .installed {
        color: var(--bs-success);
    }
</style>
<div class="card card-default">
    <div class="card-header">
        <h3 class="card-title">Available extensions</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card" title="Collapse">
                <i data-lte-icon="expand" class="fas fa-plus"></i>
                <i data-lte-icon="collapse" class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-lte-toggle="card-remove" aria-label="Remove card" title="Remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="list-group list-group-flush table-responsive">

            @foreach($extensions as $extension)
                <div class="list-group-item d-flex align-items-center">
                    <div class="ext-icon me-3">
                        <i class="fas fa-{{ $extension['icon'] }} fa-2x"></i>
                    </div>

                    <div class="flex-grow-1">
                        <a href="{{ $extension['link'] }}" target="_blank" class="fw-semibold text-decoration-none">
                            {{ $extension['name'] }}
                        </a>
                    </div>

                    @if($extension['installed'])
                        <span class="installed ms-3">
                            <i class="fas fa-check"></i>
                        </span>
                    @endif
                </div>
            @endforeach

        </div>
    </div>

    <div class="card-footer text-center">
        <a href="https://github.com/laravel-admin-extensions" target="_blank" class="uppercase">View All Extensions</a>
    </div>

</div>
