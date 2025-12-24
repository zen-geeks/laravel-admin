<form action="{!! $action !!}" pjax-container style="display: inline-block;">
    <div class="input-group input-group-sm" style="display: inline-block;">
        <input type="text" name="{{ $key }}" class="form-control grid-quick-search" style="width: 200px;" value="{{ $value }}" placeholder="{{ $placeholder }}">

        <div class="input-group-append" style="display: inline-block;">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
        </div>
    </div>
</form>