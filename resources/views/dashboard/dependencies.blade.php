<div class="card card-default">
    <div class="card-header">
        <h3 class="card-title">Dependencies</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Collapse card" title="Collapse">
                <i data-lte-icon="expand" class="fa fa-plus"></i>
                <i data-lte-icon="collapse" class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-lte-toggle="card-remove" aria-label="Remove card" title="Remove">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <!-- /.card-header -->
    <div class="card-body dependencies">
        <div class="table-responsive">
            <table class="table table-striped">
                @foreach($dependencies as $dependency => $version)
                <tr>
                    <td width="240px">{{ $dependency }}</td>
                    <td><span class="label label-primary">{{ $version }}</span></td>
                </tr>
                @endforeach
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.card-body -->
</div>

<script>
    // $('.dependencies').slimscroll({height:'510px',size:'3px'});
</script>
