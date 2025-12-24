<span class="grid-selector" data-toggle="modal" data-target="#{{ $modal }}" key="{{ $key }}" data-val="{{ $original }}">
   <a href="javascript:void(0)" class="text-muted">
       <i class="fas fa-check-square-o"></i>&nbsp;&nbsp;
       <span class="text">{{ $value }}</span>
   </a>
</span>

<style>
    .belongsto.modal tr {
        cursor: pointer;
    }

    .belongsto.modal .card {
        border-top: none;
        margin-bottom: 0;
        card-shadow: none;
    }
    .belongsto.modal .loading {
        margin: 50px;
    }
</style>

<template>
    <div class="modal fade belongsto" id="{{ $modal }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 5px;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">{{ admin_trans('admin.choose') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="loading text-center">
                        <i class="fas fa-spinner fa-pulse fa-3x fa-fw"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ admin_trans('admin.cancel') }}</button>
                    <button type="button" class="btn btn-primary submit">{{ admin_trans('admin.submit') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    var $modal = $('#{{ $modal }}');
    var $related = null;

    var update = function (callback) {
        $.ajax({
            url: "{{ $resource }}/" + $related.attr('key'),
            type: "POST",
            data: {
                '{{ $name }}': selected.length ? selected : [''],
                _token: LA.token,
                _method: 'PUT'
            },
            success: function (data) {
                callback(data);
            }
        });
    };

    @if($relation == \Encore\Admin\Grid\Displayers\BelongsTo::class)

    var selected = null;

    var load = function (url) {
        $.get(url, function (data) {
            $modal.find('.modal-body').html(data);
            $modal.find('.card-header:first').hide();

            $modal.find('input.select').each(function () {
                if ($(this).val() == selected) {
                    this.checked = true;
                }
            });
        });
    };

    $modal
        .on('show.bs.modal', function (e) {
            $related = $(e.relatedTarget);
            selected = $related.data('val');
            load("{!! $url !!}");
        })
        .on('change', 'input.select', function () {
            selected = $(this).val();
        })
        .find('.modal-footer .submit')
        .click(function () {
            update(function (data) {
                $related.data('val', selected);
                $related.find('.text').html(data.display["{{ $name }}"]);
                $related.find('a').toggleClass('text-green text-muted');

                setTimeout(function () {
                    $related.find('a').toggleClass('text-green text-muted');
                }, 2000);

                $modal.modal('toggle');
                toastr.success(data.message);
            });
        });

    @else
    var selected = [];

    var load = function (url) {
        $.get(url, function (data) {
            $modal.find('.modal-body').html(data);
            $modal.find('.card-header:first').hide();

            $modal.find('input.select').each(function () {
                if (selected.indexOf($(this).val().toString()) >= 0) {
                    this.checked = true;
                }
            });
        });
    };

    $modal
        .on('show.bs.modal', function (e) {
            $related = $(e.relatedTarget);
            selected = $related.data('val').map(String);
            load("{!! $url !!}");
        })
        .on('change', 'input.select', function () {
            var val = $(this).val().toString();

            if (this.checked) {
                if (selected.indexOf(val) < 0) {
                    selected.push(val);
                }
            } else {
                var index = selected.indexOf(val);
                if (index !== -1) {
                    selected.splice(index, 1);
                }
            }
        })
        .find('.modal-footer .submit')
        .click(function () {
            update(function (data) {
                $related.data('val', selected);
                $related.find('.text').html(data.display["{{ $name }}"]);
                $related.find('a').toggleClass('text-green text-muted');

                setTimeout(function () {
                    $related.find('a').toggleClass('text-green text-muted');
                }, 2000);

                $modal.modal('toggle');
                toastr.success(data.message);
            });
        });
    @endif


    $modal
        .on('click', '.page-item a, .filter-card a', function (e) {
            load($(this).attr('href'));
            e.preventDefault();
        })
        .on('click', 'tr', function (e) {
            var input = $(this).find('input.select')[0];
            if (input) {
                input.checked = !input.checked;
                $(input).trigger('change');
            }
            e.preventDefault();
        })
        .on('submit', '.card-header form', function (e) {
            load($(this).attr('action') + '&' + $(this).serialize());
            e.preventDefault();
            return false;
        });

</script>
