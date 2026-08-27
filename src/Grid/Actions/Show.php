<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\RowAction;

class Show extends RowAction
{
    /**
     * @return array|null|string
     */
    public function name()
    {
        return __('admin.show');
    }

    /**
     * @return string
     */
    public function href()
    {
        return admin_url("{$this->getResource()}/{$this->getKey()}", absolute: false);
    }
}
