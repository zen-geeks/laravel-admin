<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;

class BelongsTo extends Select
{
    use BelongsToRelation;

    protected function addScript()
    {
        $script = <<<SCRIPT
;(function () {

    var grid = $('.belongsto-{$this->column()}');
    var modal = $('#{$this->modalID}');
    var table = grid.find('.grid-table');
    var selected = $("{$this->getElementClassSelector()}").val();
    var row = null;

    // open modal
    grid.find('.select-relation').click(function (e) {
        modal.modal('show');
        e.preventDefault();
    });

    // remove row
    grid.on('click', '.grid-row-remove', function () {
        selected = null;
        $(this).parents('tr').remove();
        $("{$this->getElementClassSelector()}").val(null);

        var empty = $('.belongsto-{$this->column()}').find('template.empty').html();
        table.find('tbody').append(empty);
    });

    var load = function (url) {
        $.get(url, function (data) {
            modal.find('.modal-body').html(data);
            modal.find('.card-header:first').hide();

            modal.find('input.select').each(function () {
                if ($(this).val() == selected) {
                    this.checked = true;
                }
            });
        });
    };

    var update = function (callback) {

        $("{$this->getElementClassSelector()}")
            .select2({data: [selected]})
            .val(selected)
            .trigger('change')
            .next()
            .addClass('hide');

        if (row) {
            row.find('td:last a').removeClass('hide');
            row.find('td:first').remove();
            table.find('tbody').empty().append(row);
        }

        callback();
    };

    modal
        .on('show.bs.modal', function () {
            load("{$this->getLoadUrl()}");
        })
        .on('click', '.page-item a, .filter-box a', function (e) {
            load($(this).attr('href'));
            e.preventDefault();
        })
        .on('click', 'tr', function (e) {
            var input = $(this).find('input.select')[0];
            if (input) {
                input.checked = true;
                $(input).trigger('change');
            }
            e.preventDefault();
        })
        .on('submit', '.card-header form', function (e) {
            load($(this).attr('action') + '&' + $(this).serialize());
            return false;
        })
        .on('change', 'input.select', function () {
            row = $(this).parents('tr');
            selected = $(this).val();
        })
        .find('.modal-footer .submit')
        .click(function () {
            update(function () {
                modal.modal('toggle');
            });
        });

})();
SCRIPT;

        Admin::script($script);

        return $this;
    }

    protected function getOptions()
    {
        $options = [];

        if ($value = $this->value()) {
            $options = [$value => $value];
        }

        return $options;
    }
}
