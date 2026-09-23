<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Field;

class Display extends Field
{
    public function copyable()
    {
        $this->customFormat = function ($value) {
            $id = uniqid();
            return <<<HTML
<span id="{$id}">{$value}</span>
<a href="javascript:void(0)" data-copy-target="#{$id}" class="float-end color-secondary">
    <i class="far fa-copy"></i>
</a>
HTML;
        };

        return $this;
    }
}
