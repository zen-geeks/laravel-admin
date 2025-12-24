<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Admin;

class RowSelector extends AbstractDisplayer
{
    public function display()
    {
        Admin::script($this->script());

        return <<<EOT
<input type="checkbox" class="{$this->grid->getGridRowName()}-checkbox" data-id="{$this->getKey()}"  autocomplete="off"/>
EOT;
    }

    protected function script()
    {
        $all = $this->grid->getSelectAllName();
        $row = $this->grid->getGridRowName();
        $selectedText = trans('admin.grid_items_selected');

        return <<<EOT
$('.{$row}-checkbox').on('change', function () {

    var id = $(this).data('id');

    if (this.checked) {
        $.admin.grid.select(id);
        $(this).closest('tr').addClass('bg-warning');
    } else {
        $.admin.grid.unselect(id);
        $(this).closest('tr').removeClass('bg-warning');
    }

    var selected = $.admin.grid.selected().length;

    $('.{$all}-btn').toggle(selected > 0);
    $('.{$all}-btn .selected')
        .html("{$selectedText}".replace('{n}', selected));
});
EOT;
    }
}
