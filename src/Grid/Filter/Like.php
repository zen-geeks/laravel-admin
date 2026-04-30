<?php

namespace Encore\Admin\Grid\Filter;

use Illuminate\Support\Arr;

class Like extends AbstractFilter
{
    protected string $exprFormat = '%{value}%';
    protected string $operator = 'like';
    public string $separator = '||';
    public bool $multiple = false;

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return array|mixed|void
     */
    public function condition($inputs)
    {
        $value = Arr::get($inputs, $this->column);

        if (is_array($value) || empty($value)) {
            return;
        }

        $this->value = $value;

        if ($this->multiple) {
            $values = array_filter(array_map('trim', explode($this->separator, $value)));
            if (empty($values))
                return;

            return $this->buildCondition(function ($query) use ($values) {
                foreach ($values as $val) {
                    $expr = str_replace('{value}', $val, $this->exprFormat);
                    $query->orWhere($this->column, $this->operator, $expr);
                }
            });
        } else {
            $expr = str_replace('{value}', $this->value, $this->exprFormat);
            return $this->buildCondition($this->column, $this->operator, $expr);
        }
    }

    public function multiple(?string $separator = null): static
    {
        if ($separator)
            $this->separator = $separator;
        $this->multiple = true;
        return $this;
    }
}
