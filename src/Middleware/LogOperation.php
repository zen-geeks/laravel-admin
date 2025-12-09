<?php

namespace Encore\Admin\Middleware;

use Encore\Admin\Auth\Database\OperationLog as OperationLogModel;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogOperation
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if ($this->shouldLogOperation($request)) {
            $data = $this->getData($request);
            $data = self::cleanData($data);

            if (empty($data))
                return $next($request);

            $log = [
                'user_id' => Admin::user()->id,
                'path'    => substr($request->path(), 0, 255),
                'method'  => $request->method(),
                'ip'      => $request->getClientIp(),
                'input'   => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];

            try {
                OperationLogModel::create($log);
            } catch (\Exception $exception) {
                // pass
            }
        }

        return $next($request);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function shouldLogOperation(Request $request)
    {
        return config('admin.operation_log.enable')
            && !$this->inExceptArray($request)
            && $this->inAllowedMethods($request->method())
            && Admin::user();
    }

    /**
     * Whether requests using this method are allowed to be logged.
     *
     * @param string $method
     *
     * @return bool
     */
    protected function inAllowedMethods($method)
    {
        $allowedMethods = collect(config('admin.operation_log.allowed_methods'))->filter();

        if ($allowedMethods->isEmpty()) {
            return true;
        }

        return $allowedMethods->map(function ($method) {
            return strtoupper($method);
        })->contains($method);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach (config('admin.operation_log.except') as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            $methods = [];

            if (Str::contains($except, ':')) {
                list($methods, $except) = explode(':', $except);
                $methods = explode(',', $methods);
            }

            $methods = array_map('strtoupper', $methods);

            if ($request->is($except) &&
                (empty($methods) || in_array($request->method(), $methods))) {
                return true;
            }
        }

        return false;
    }

    protected function getData(Request $request): array
    {
        $input = $request->input();

        // add file names
        if (!empty($request->allFiles())) {
            foreach ($request->allFiles() as $key => $files) {
                $names = array_map(function ($file) {
                    return $file->getClientOriginalName();
                }, is_array($files) ? $files : [$files]);

                $input[$key] = count($names) === 1 ? $names[0] : $names;
            }
        }

        // _handle_action_
        if ($request->route()?->getName() === 'admin.handle-action') {
            if (!empty($input['_action'])) {
                // deleting action from grid
                if (str_ends_with($input['_action'], '_Grid_Actions_Delete') && isset($input['_key'], $input['_model'])) {
                    $model = $this->getModel($request);
                    if (!empty($model))
                        $input['data'] = $model::find($input['_key'])?->toArray();
                }
            }

            // delete service keys
            $service_keys = [
                'settings', // UI configuration that doesn’t add meaningful log context
                'props', // Repetitive UI data that offers no additional insight
                '_type', // Duplicates information already conveyed by the action class
                '_input', // Low-value form flag with no practical logging benefit
            ];
            foreach ($service_keys as $key)
                unset($input[$key]);

            return $input;
        } elseif ($request->route()?->getName() === 'admin.login-post' && isset($input['password'])) {
            $input['password'] = mb_substr(Str::mask((string)$input['password'], '*', 3), 0, 6);
        }

        $id = $this->extractId($request);
        if ($id === null)
            return $input;

        $model = $this->getModel($request);
        if ($model === null)
            return $input;

        // on delete return DB values
        if ($request->method() === 'DELETE') {
            $input['data'] = is_array($id)
                ? $model->whereIn($model->getKeyName(), $id)->get()?->toArray() // batch-delete
                : $model->find($id)?->toArray();
            return $input;
        }

        $row = $model->find($id)?->toArray();
        if ($row === null)
            return $input;

        $log_data = [];
        foreach ($input as $key => $val) {
            $val = match ($val) {
                'on' => 1,
                'off' => 0,
                default => $val
            };

            if (isset($row[$key]) && $val != $row[$key]) {
                $log_data[$key] = [
                    'old' => $row[$key],
                    'new' => $val
                ];
            } elseif (!isset($row[$key])) {
                $log_data[$key] = $val;
            }
        }

        return $log_data;
    }

    protected function extractId(Request $request): null|array|string
    {
        $params = array_values($request->route()->parameters());
        if (empty($params))
            return null;

        $ids = array_filter(array_map('trim', explode(',', (string) end($params))));
        return count($ids) === 1 ? $ids[0] : $ids;
    }

    protected function getModel(Request $request): ?\Illuminate\Database\Eloquent\Model
    {
        $controller = $request->route()->getController();

        if (!empty($request->input('_model'))) {
            $model_class = str_replace('_', '\\', $request->input('_model'));
        } else {
            // Trying to get classname from controller name
            $short = class_basename($controller);
            $entity = str_replace('Controller', '', $short);
            $model_class = "App\\Models\\$entity";
        }
        if (class_exists($model_class))
            return new $model_class;

        if (method_exists($controller, 'form')) {
            $form = (fn() => $this->form())->call($controller);
            return $form->model();
        }

        return null;
    }

    private static function cleanData(null|array|string $data): null|array|string
    {
        if ($data === null)
            return null;

        if (is_array($data)) {
            $clean = [];

            foreach ($data as $key => $value) {
                $value = self::cleanData($value);

                if ($value !== null && (!is_array($value) || !empty($value)))
                    $clean[$key] = $value;
            }
            return empty($clean) ? null : $clean;
        }
        return $data;
    }
}
