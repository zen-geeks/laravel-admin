<?php

namespace Encore\Admin\Form\Field;

class Decimal extends Text
{
    protected static $js = [
        '/vendor/laravel-admin/input-mask/jquery.inputmask.bundle.min.js',
    ];

    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'alias'      => 'decimal',
        'rightAlign' => true,
    ];

    public function render()
    {
        $this->inputmask($this->options);
        $this->setWidth('auto', 2);

        $this->prepend('<i class="fas '.$this->icon.' fa-fw"></i>')
            ->defaultAttribute('style', 'width: 130px');

        return parent::render();
    }
}
