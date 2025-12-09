<?php

namespace Encore\Admin\Actions;

use Encore\Admin\Admin;

abstract class RowSimpleAction extends RowAction
{
    /**
     * @var string
     */
    public $selectorPrefix = '.grid-row-action-s-';

    /**
     * @var array
     */
    protected static array $actions = [];

    /**
     * @var array
     */
    private static array $is_rendered = [];

    /**
     * @var array
     */
    private static array $select_options = [];

    public function render(): string
    {
        if ($href = $this->href()) {
            return "<a href='{$href}'>{$this->name()}</a>";
        }

        if (!is_null($this->interactor)) {
            $this->renderInteractor();
        } else {
            $this->renderDefault();
        }

        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='%s' %s>%s</a>",
            $this->getKey(),
            ltrim($this->actionSelector(), '.'),
            $attributes,
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
    }

    private function actionSelector(): string
    {
        $class = get_called_class();

        if (!isset(static::$actions[$class])) {
            static::$actions[$class] = $this->selectorPrefix.(count(static::$actions) + 1);
        }
        return static::$actions[$class];
    }

    private function renderDefault(): void
    {
        $render_key = get_called_class().'-default';
        if (array_key_exists($render_key, static::$is_rendered))
            return;
        static::$is_rendered[$render_key] = true;

        $parameters = json_encode($this->parameters());

        $script = <<<SCRIPT

            (function ($) {
                $('{$this->actionSelector()}').off('{$this->event}').on('{$this->event}', function() {
                    var target = $(this);
                    var data = target.data();
                    Object.assign(data, {$parameters});
                    {$this->actionScript()}
                    {$this->buildActionPromise()}
                    {$this->handleActionPromise()}
                });
            })(jQuery);

        SCRIPT;

        Admin::script($script);
    }

    private function renderInteractor(): void
    {
        if ($this->interactor instanceof Interactor\Form) {
            $this->renderForm();
        } elseif ($this->interactor instanceof Interactor\Dialog) {
            $this->renderDialog();
        }
    }

    private function renderDialog(): void
    {
        call_user_func([$this, 'dialog']);
        $settings = (new \ReflectionClass($this->interactor))->getProperty('settings')->getValue($this->interactor);
        if (!empty($settings)) {
            if (empty($settings['text'])) {
                unset($settings['text']);
            }
            $this->attribute('data-settings', json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $render_key = get_called_class().'-dialog';
        if (array_key_exists($render_key, static::$is_rendered))
            return;
        static::$is_rendered[$render_key] = true;

        $parameters = json_encode($this->parameters());
        $script = <<<SCRIPT

            (function ($) {
                $('{$this->actionSelector()}').off('{$this->event}').on('{$this->event}', function() {
                    var target = $(this);
                    var data = target.data();
                    var settings = JSON.parse(target.attr('data-settings'));
                    Object.assign(data, {$parameters});
                    {$this->actionScript()}
                    var swalOptions = $.extend({$this->getDefaultSettings()}, {
                        preConfirm: function(input) {
                            return new Promise(function(resolve, reject) {
                                Object.assign(data, {
                                    _token: $.admin.token,
                                    _action: '{$this->getCalledClass()}',
                                    _input: input,
                                });

                                $.ajax({
                                    method: '{$this->getMethod()}',
                                    url: '{$this->getHandleRoute()}',
                                    data: data,
                                    success: function (data) {
                                        resolve(data);
                                    },
                                    error:function(request){
                                        reject(request);
                                    }
                                });
                            });
                        }
                    }, settings);

                    var process = $.admin.swal(swalOptions).then(function(result) {
                        if (typeof result.dismiss !== 'undefined') {
                            return Promise.reject();
                        }

                        var response = typeof result.status === "boolean" ? result : result.value;;

                        return [response, target];
                    });

                    {$this->handleActionPromise()}
                });
            })(jQuery);

            SCRIPT;
        Admin::script($script);
    }

    private function renderForm(): void
    {
        $parameters = json_encode($this->parameters());
        $modal_id = ltrim($this->actionSelector(), '.').'-modal';
        $this->attribute('modal', $modal_id);

        call_user_func([$this, 'form'], $this->getRow());
        $props = [];
        $fields = $this->getInteractorFields();
        foreach ($fields as $row) {
            $tmp = [
                'id' => $row['id'],
                'value' => $row['value'],
                'label' => $row['label'],
            ];

            if (!empty($row['rules']) && in_array('required', $row['rules']))
                $tmp['is_required'] = 1;

            if (array_key_exists('options', $row)) {
                if (empty(static::$select_options)) {
                    Admin::script(<<<SCRIPT
                        var modal_select_options = {};
                    SCRIPT);
                }
                $hash = md5(serialize($row['options']));
                if (empty(static::$select_options[$hash])) {
                    static::$select_options[$hash] = true;
                    $options_json = json_encode($row['options'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    Admin::script(<<<SCRIPT
                        modal_select_options["{$hash}"] = {$options_json};
                    SCRIPT);
                }
                $tmp['options_id'] = $hash;
            }

            $props[] = $tmp;
        }

        if (!empty($props))
            $this->attribute('data-props', json_encode($props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $confirm = (new \ReflectionClass($this->interactor))->getProperty('confirm')->getValue($this->interactor);
        if (!empty($confirm)) {
            $this->attribute('data-settings', json_encode(['title' => $confirm], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $render_key = get_called_class().'-form';
        if (array_key_exists($render_key, static::$is_rendered))
            return;
        static::$is_rendered[$render_key] = true;

        $this->interactor->addModalHtml();

        $action_script = <<<SCRIPT
            var process = new Promise(function (resolve,reject) {
                Object.assign(data, {
                    _token: $.admin.token,
                    _action: '{$this->getCalledClass()}',
                });

                var formData = new FormData(form);
                for (var key in data) {
                    formData.append(key, typeof data[key] === 'object' ? JSON.stringify(data[key]) : data[key]);
                }

                $.ajax({
                    method: '{$this->getMethod()}',
                    url: '{$this->getHandleRoute()}',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        resolve([data, target]);
                        if (data.status === true) {
                            modal.modal('hide');
                        }
                        $(':submit', modal).button('reset');
                    },
                    error:function(request){
                        reject(request);
                    }
                });
            });
SCRIPT;

        if (!empty($confirm)) {
            $action_script = <<<PROMISE
            var settings = JSON.parse(target.attr('data-settings') ?? {});
            var swalOptions = $.extend({$this->getDefaultSettings()}, settings, {
                preConfirm: function() {
                    {$action_script}
                    return process;
                }
            });
            var process = $.admin.swal(swalOptions).then(function(result) {
                if (typeof result.dismiss !== 'undefined')
                    return Promise.reject();
                var result = result.value[0];
                var response = typeof result.status === "boolean" ? result : result.value;
                return [response, target];
            });
PROMISE;
        }

        $script = <<<SCRIPT

            (function ($) {
                $('#{$this->interactor->getModalId()}').attr('id', '{$modal_id}')

                $('{$this->actionSelector()}').off('{$this->event}').on('{$this->event}', function() {
                    var target = $(this);
                    var data = target.data();
                    var modalId = target.attr('modal');
                    var modal = $('#'+modalId);
                    var props = JSON.parse(target.attr('data-props'));

                    if (props.length > 0) {
                        let item;
                        for (let i in props) {
                            item = $('#'+props[i].id, modal).length > 0 ? $('#'+props[i].id, modal) : $('[name="'+props[i].id+'"]', modal);
                            if (item.length) {
                                if (props[i].options_id !== undefined && modal_select_options[props[i].options_id] !== undefined) {
                                    item.empty();
                                    item.append($('<option value=""></option>'));
                                    $.each(modal_select_options[props[i].options_id], function(key, value) {
                                      item.append($('<option></option>').attr('value', key).text(value));
                                    });
                                    item.trigger('change');
                                }

                                item.parents('.form-group').find('label').text(props[i].label !== null ? props[i].label : '');
                                item.val(props[i].value !== null ? props[i].value : '');

                                if (props[i].is_required)
                                    item.parents('.form-group').find('label').addClass('asterisk');
                            }
                        }
                    }

                    Object.assign(data, {$parameters});
                    {$this->actionScript()}
                    modal.modal('show');
                    $(':submit', modal).button('reset');
                    $('form', modal).off('submit').on('submit', function (e) {
                        $(':submit', e.target).button('loading');
                        e.preventDefault();
                        var form = this;
                        {$action_script}
                        {$this->handleActionPromise()}
                    });
                });
            })(jQuery);

            SCRIPT;
        Admin::script($script);
    }

    private function getInteractorFields(): array
    {
        $fields = [];
        $class = (new \ReflectionClass($this->interactor))->getProperty('fields')->getValue($this->interactor);
        if (!empty($class)) {
            foreach ($class as $row) {
                $properties = (new \ReflectionClass($row))->getProperties();

                $tmp = [];
                foreach ($properties as $property) {
                    $tmp[$property->getName()] = $property->getValue($row);
                }
                $fields[] = $tmp;
            }
        }
        return $fields;
    }

    private function getDefaultSettings(): string
    {
        return json_encode([
            'type'                => 'question',
            'showCancelButton'    => true,
            'showLoaderOnConfirm' => true,
            'confirmButtonText'   => trans('admin.submit'),
            'cancelButtonText'    => trans('admin.cancel'),
        ], JSON_PRETTY_PRINT);
    }
}
