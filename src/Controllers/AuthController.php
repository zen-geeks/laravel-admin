<?php

namespace Encore\Admin\Controllers;

use BaconQrCode\Writer;
use BaconQrCode\Renderer;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        return $content
            ->title(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }

    public function show2FaForm(Request $request): View|RedirectResponse
    {
        $user = Admin::user();

        if (!$user->is_google2fa || empty($user->google2fa_secret)) {
            return redirect()->route('admin.home');
        }

        if ($request->isMethod('POST')) {
            $validator = Validator::make(
                array_merge($request->all(), ['google2fa_secret' => $user->google2fa_secret],),
                [
                    'code' => 'required|string|digits:6',
                    'google2fa_secret' => 'required'
                ],
                ['google2fa_secret.required' => 'QR code is not installed for you, please contact our support team.']
            );

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $code = $request->string('code', '')->trim()->value();
            $google2fa = new Google2FA();
            $is_valid = $google2fa->verifyKey($user->google2fa_secret, $code);

            if ($is_valid) {
                $user->google2fa_remember_token = $user->remember_token;
                $user->save();
                $request->session()->put('2fa_admin', 'valid');
                return redirect()->route('admin.home');
            } else {
                return back()->withErrors(new MessageBag(['code' => 'Code is invalid']));
            }
        }

        return view('admin::google2fa.index');
    }

    public function set2Fa(Request $request): View|RedirectResponse
    {
        $user = Admin::user();

        if (!$user->is_google2fa || !empty($user->google2fa_secret)) {
            return redirect()->route('admin.home');
        }

        $qr_code = $request->session()->get('2fa_admin_set');
        if (!$qr_code) {
            $qr_code = $this->getQRCode($user->username);
            $request->session()->put('2fa_admin_set', $qr_code);
        }

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                    'code' => 'bail|required|string|digits:6',
                ]
            );
            if ($validator->fails()) {
                return back()->withErrors($validator);
            }
            $code = $request->string('code', '')->trim()->value();

            $google2fa = new Google2FA();
            $is_valid = $google2fa->verifyKey($qr_code['secret'], $code);

            if ($is_valid) {
                $user->google2fa_secret = $qr_code['secret'];
                $user->google2fa_remember_token = $user->remember_token;
                $user->save();
                // стираем код и выставляем 2fa проверенной
                $request->session()->remove('2fa_admin_set');
                $request->session()->put('2fa_admin', 'valid');

                return redirect()->route('admin.home');
            } else {
                return back()->withErrors(new MessageBag(['code' => 'Code is invalid']));
            }
        }

        return view('admin::google2fa.set', [
            'username'      => $user->username,
            'secret_key'    => $qr_code['secret'],
            'qr_code_image' => $qr_code['image'],
        ]);
    }

    protected function getQRCode(string $login, string $secret_key = null): array
    {
        $google2fa = new Google2FA();

        if (empty($secret_key)) {
            $secret_key = $google2fa->generateSecretKey();
        }

        $google2fa_url = $google2fa->getQRCodeUrl(config('admin.name'), $login, $secret_key);
        $writer = new Writer(
            new Renderer\ImageRenderer(
                new Renderer\RendererStyle\RendererStyle(150),
                new Renderer\Image\SvgImageBackEnd()
            )
        );
        $qrcode_image = base64_encode($writer->writeString($google2fa_url));

        return ['secret' => $secret_key, 'image' => $qrcode_image];
    }
}
