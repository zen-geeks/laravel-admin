@extends('admin::layouts.auth')

@section('content')
    <p>
        <h2>{{ trans('admin.ext.2fa.title') }}</h2>
        <span>{!! trans('admin.ext.2fa.description_set') !!}</span>
    </p>
    @if($qr_code_image)
        <img src="data:image/svg+xml;base64, {{ $qr_code_image}}" />
    @endif

    <div class="form-group">
        <label for="secret-key">{{ trans('admin.ext.2fa.secret_key') }}</label>
        <input type="text" class="form-control" id="secret_key" name="secret_key" readonly value="{{ $secret_key  }}">
    </div>

    <form method="POST" action="">
        @csrf
        <div class="input-group">
            <input
                    type="text"
                    class="form-control {{ $errors->has('code') || $errors->has('google2fa_secret') ? 'is-invalid' : ''}} "
                    placeholder="{{ trans('admin.ext.2fa.enter_code') }}"
                    name="code"
                    minlength="6"
                    maxlength="6"
                    required>
            <button class="btn btn-secondary" type="submit">{{ trans('admin.ext.2fa.check_code') }}</button>
            @if($errors->has('code'))
                <div class="invalid-feedback">
                    {{ $errors->first('code') }}
                </div>
            @endif
            @if($errors->has('google2fa_secret'))
                <div class="invalid-feedback">
                    {{ $errors->first('google2fa_secret') }}
                </div>
            @endif
        </div>
    </form>
@endsection
