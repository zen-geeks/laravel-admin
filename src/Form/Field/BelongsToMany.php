<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Admin;

class BelongsToMany extends MultipleSelect
{
    use BelongsToRelation;

    protected function addScript()
    {
        $script = <<<SCRIPT
;(function () {

    var grid = $('.belongstomany-{$this->column()}');
    var modal = $('#{$this->modalID}');
    var table = grid.find('.grid-table');
    var selected = $("{$this->getElementClassSelector()}").val() || [];
    var rows = {};

    table.find('tbody').children().each(function (_, tr) {
        var btn = $(tr).find('.grid-row-remove');
        if (btn.length) {
            rows[btn.data('key')] = $(tr);
        }
    });

    grid.find('.select-relation').click(function (e) {
        modal.modal('show');
        e.preventDefault();
    });

    grid.on('click', '.grid-row-remove', function () {
        var val = $(this).data('key').toString();
        var index = selected.indexOf(val);

        if (index !== -1) {
            selected.splice(index, 1);
            delete rows[val];
        }

        $(this).parents('tr').remove();
        $("{$this->getElementClassSelector()}").val(selected);

        if (selected.length === 0) {
            var empty = $('.belongstomany-{$this->column()}').find('template.empty').html();
            table.find('tbody').append(empty);
        }
    });

    var load = function (url) {
        $.get(url, function (data) {
            modal.find('.modal-body').html(data);
            modal.find('.card-header:first').hide();

            modal.find('input.select').each(function () {
                if (selected.indexOf($(this).val().toString()) >= 0) {
                    this.checked = true;
                }
            });
        });
    };

    var update = function (callback) {

        $("{$this->getElementClassSelector()}")
            .select2({data: selected})
            .val(selected)
            .trigger('change')
            .next()
            .addClass('hide');

        table.find('tbody').empty();

        Object.values(rows).forEach(function (row) {
            row.find('td:last a').removeClass('hide');
            row.find('td.column-__modal_selector__').remove();
            table.find('tbody').append(row);
        });

        if (selected.length === 0) {
            var empty = $('.belongstomany-{$this->column()}').find('template.empty').html();
            table.find('tbody').append(empty);
        } else {
            table.find('.empty-grid').parent().remove();
        }

        callback();
    };

    modal
        .on('show.bs.modal', function () {
            load("{$this->getLoadUrl(1)}");
        })
        .on('click', '.page-item a, .filter-box a', function (e) {
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
        })
        .on('change', 'input.select', function () {
            var val = $(this).val();

            if (this.checked) {
                if (selected.indexOf(val) < 0) {
                    selected.push(val);
                    rows[val] = $(this).parents('tr');
                }
            } else {
                var index = selected.indexOf(val);
                if (index !== -1) {
                    selected.splice(index, 1);
                    delete rows[val];
                }
            }
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

        if ($this->value()) {
            $options = array_combine($this->value(), $this->value());
        }

        return $options;
    }
}
