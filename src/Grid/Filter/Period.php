<?php

declare(strict_types=1);

namespace Encore\Admin\Grid\Filter;

use Encore\Admin\Admin;

class Period extends AbstractFilter
{
    const LAST_5_MIN = 'last_5_min';
    const LAST_15_MIN = 'last_15_min';
    const LAST_30_MIN = 'last_30_min';
    const LAST_HOUR = 'last_hour';
    const LAST_3_HOURS = 'last_3_hours';
    const LAST_6_HOURS = 'last_6_hours';
    const LAST_12_HOURS = 'last_12_hours';
    const LAST_24_HOURS = 'last_24_hours';
    const LAST_2_DAYS = 'last_2_days';
    const LAST_3_DAYS = 'last_3_days';
    const LAST_7_DAYS = 'last_7_days';
    const LAST_14_DAYS = 'last_14_days';
    const LAST_30_DAYS = 'last_30_days';
    const LAST_90_DAYS = 'last_90_days';
    const LAST_YEAR = 'last_year';
    const LAST_2_YEARS = 'last_2_years';
    const LAST_3_YEARS = 'last_3_years';
    const YESTERDAY = 'yesterday';
    const DAY_BEFORE_YESTERDAY = 'day_before_yesterday';
    const PREVIOUS_WEEK = 'previous_week';
    const PREVIOUS_MONTH = 'previous_month';
    const PREVIOUS_YEAR = 'previous_year';
    const TODAY = 'today';
    const THIS_WEEK = 'this_week';
    const THIS_MONTH = 'this_month';
    const THIS_YEAR = 'this_year';
    const CUSTOM_DATE = 'custom_date';
    const CUSTOM_DATETIME = 'custom_datetime';

    const PERIODS = [
        self::LAST_5_MIN => '-5 minutes',
        self::LAST_15_MIN => '-15 minutes',
        self::LAST_30_MIN => '-30 minutes',
        self::LAST_HOUR => '-1 hour',
        self::LAST_3_HOURS => '-3 hours',
        self::LAST_6_HOURS => '-6 hours',
        self::LAST_12_HOURS => '-12 hours',
        self::LAST_24_HOURS => '-1 day',
        self::LAST_2_DAYS => '-2 days',
        self::LAST_3_DAYS => '-3 days',
        self::LAST_7_DAYS => '-7 days',
        self::LAST_14_DAYS => '-14 days',
        self::LAST_30_DAYS => '-30 days',
        self::LAST_90_DAYS => '-90 days',
        self::LAST_YEAR => '-1 year',
        self::LAST_2_YEARS => '-2 years',
        self::LAST_3_YEARS => '-3 years',
        self::YESTERDAY => [
            'start' => 'yesterday 00:00',
            'end' => 'yesterday 23:59'
        ],
        self::DAY_BEFORE_YESTERDAY => [
            'start' => '-2 days 00:00',
            'end' => '-2 days 23:59'
        ],
        self::PREVIOUS_WEEK => [
            'start' => 'last week monday 00:00',
            'end' => 'last week sunday 23:59'
        ],
        self::PREVIOUS_MONTH => [
            'start' => 'first day of last month 00:00',
            'end' => 'last day of last month 23:59'
        ],
        self::PREVIOUS_YEAR => [
            'start' => 'first day of january last year 00:00',
            'end' => 'last day of december last year 23:59'
        ],
        self::TODAY => 'today',
        self::THIS_WEEK => 'this week monday 00:00',
        self::THIS_MONTH => 'first day of this month 00:00',
        self::THIS_YEAR => 'first day of january this year 00:00',
        self::CUSTOM_DATE => null,
        self::CUSTOM_DATETIME => null,
    ];

    // default periods
    protected array $periods = [
        self::LAST_5_MIN,
        self::LAST_15_MIN,
        self::LAST_30_MIN,
        self::LAST_HOUR,
        self::LAST_3_HOURS,
        self::LAST_6_HOURS,
        self::LAST_12_HOURS,
        self::LAST_24_HOURS,
        self::LAST_2_DAYS,
        self::LAST_7_DAYS,
        self::TODAY,
        self::YESTERDAY,
        self::DAY_BEFORE_YESTERDAY,
        self::THIS_WEEK,
        self::PREVIOUS_WEEK,
        self::CUSTOM_DATE,
        self::CUSTOM_DATETIME,
    ];

    const CUSTOM_FORMATS = [
        self::CUSTOM_DATE => 'Y-m-d',
        self::CUSTOM_DATETIME => 'Y-m-d H:i:s',
    ];

    protected bool $expand = true;

    protected $view = 'admin::filter.period';

    /**
     * @param array $periods
     * @return $this
     */
    public function setPeriods(array $periods): static
    {
        if (!empty($periods)) {
            $res = [];
            foreach ($periods as $period) {
                if (array_key_exists($period, self::PERIODS))
                    $res[] = $period;
            }

            if (!empty($res))
                $this->periods = $res;
        }

        return $this;
    }

    /**
     * @param $inputs
     * @return array|array[]|mixed|void|null
     */
    public function condition($inputs)
    {
        $type = $inputs[$this->column]['type'] ?? $this->getDefault();
        if ($this->ignore || !$type || !array_key_exists($type, self::PERIODS))
            return;

        if ($type === self::CUSTOM_DATE || $type === self::CUSTOM_DATETIME) {
            return $this->handleCustomDate($inputs, $type);
        } elseif (is_array(self::PERIODS[$type])) {
            return $this->buildDateRangeCondition($this->getDateTime(self::PERIODS[$type]['start']), $this->getDateTime(self::PERIODS[$type]['end']));
        } else {
            return $this->buildCondition($this->column, '>=', $this->getDateTime(self::PERIODS[$type]));
        }
    }

    /**
     * @param string $modifier
     * @return string
     */
    protected function getDateTime(string $modifier): string
    {
        return now()->modify($modifier)->toDateTimeString();
    }

    /**
     * @param $start
     * @param $end
     * @return array|array[]|mixed
     */
    protected function buildDateRangeCondition($start, $end): mixed
    {
        $this->query = 'whereBetween';
        return $this->buildCondition($this->column, [$start, $end]);
    }

    /**
     * @param array $inputs
     * @param string $type
     * @return array|array[]|mixed
     */
    protected function handleCustomDate(array $inputs, string $type): mixed
    {
        $value = $inputs[$this->column][$type] ?? null;
        if (!$value)
            return null;

        $format = self::CUSTOM_FORMATS[$type];
        foreach ($value as $key => $row) {
            $date = \DateTime::createFromFormat($format, $row);
            if (!$date)
                return null;

            $value[$key] = $date->format($format);
        }

        if ($type === self::CUSTOM_DATE && !empty($value['end']))
            $value['end'] .= ' 23:59:59';

        return match (true) {
            !isset($value['start']) => $this->buildCondition($this->column, '<=', $value['end']),
            !isset($value['end']) => $this->buildCondition($this->column, '>=', $value['start']),
            default => $this->buildDateRangeCondition($value['start'], $value['end']),
        };
    }

    /**
     * @return array
     */
    protected function getPeriods(): array
    {
        $result = [];
        foreach ($this->periods as $value) {
            $result[$value] = mb_ucfirst(str_replace('_', ' ', $value));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function variables(): array
    {
        $this->prepare();
        $params = request()->all();
        return array_merge(parent::variables(), [
            'periods' => $this->getPeriods(),
            'params' => $params,
            'period_type' => $params[$this->column]['type'] ?? $this->getDefault(),
        ]);
    }

    /**
     * @return void
     */
    protected function prepare(): void
    {
        $script = '';

        $has_custom_date = in_array(self::CUSTOM_DATE, $this->periods, true);
        $has_custom_datetime = in_array(self::CUSTOM_DATETIME, $this->periods, true);

        if ($has_custom_date || $has_custom_datetime) {
            $script .= <<<EOT
                $('#{$this->column}_type').on('change', function() {
                    let toggle_id = 'toggle_{$this->column}_'+$(this).val();
                    $('div[id^="toggle_{$this->column}_"]').addClass('d-none');
                    if ($('#'+toggle_id).length > 0) {
                        $('#'+toggle_id).removeClass('d-none');
                    }
                });

            EOT;

            if ($has_custom_datetime) {
                $script .= <<<EOT
                $('#{$this->column}_start').datetimepicker({"format":"YYYY-MM-DD HH:mm:ss","locale":"en", "icons": { "time": 'far fa-clock' }});
                $('#{$this->column}_end').datetimepicker({"format":"YYYY-MM-DD HH:mm:ss","locale":"en","useCurrent":false, "icons": { "time": 'far fa-clock' }});
                $("#{$this->column}_start").on("change.datetimepicker", function (e) {
                    $('#{$this->column}_end').datetimepicker('minDate', e.date);
                });
                $("#{$this->column}_end").on("change.datetimepicker", function (e) {
                    $('#{$this->column}_start').datetimepicker('maxDate', e.date);
                });
            EOT;
            }
        }

        Admin::script($script);
    }

    /**
     * @return string|null
     */
    protected function getDefault(): ?string
    {
        if ($this->defaultValue && in_array($this->defaultValue, $this->periods, true)) {
            return $this->defaultValue;
        }
        return null;
    }

    /**
     * @param string $column
     * @return bool
     */
    public static function isNotFilled(string $column): bool
    {
        $request = request()->query($column);
        if (!$request)
            return true;

        $type = $request['type'] ?? null;

        return (is_null($type) || in_array($type, [self::CUSTOM_DATE, self::CUSTOM_DATETIME]))
            && empty($request[$type]['start'])
            && empty($request[$type]['end']);
    }
}
