<?php

namespace Encore\Admin\Grid\Displayers;

use Encore\Admin\Facades\Admin;

/**
 * Class Copyable.
 *
 * @see https://codepen.io/shaikmaqsood/pen/XmydxJ
 */
class Copyable extends AbstractDisplayer
{
    protected function addScript()
    {
        $script = <<<SCRIPT
$('#{$this->grid->tableID}').on('click','.grid-column-copyable',(function (e) {
    var content = $(this).data('content');
    var temp = $('<input>');
    $("body").append(temp);
    temp.val(content).select();
    document.execCommand("copy");
    temp.remove();
    $(this).tooltip({title: 'Copied!', placement: 'bottom'}).tooltip('show');
}));
SCRIPT;

        Admin::script($script);
    }

    public function display(): string
    {
        $content = $this->getColumn()->getOriginal();
        if (is_null($content) || $content === '')
            return '';

        $this->addScript();

        return <<<HTML
<a href="javascript:void(0);" class="grid-column-copyable text-muted" data-content="{$content}"><i class="fa fa-copy"></i></a>&nbsp;{$this->getValue()}
HTML;
    }
}
