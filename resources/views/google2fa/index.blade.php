@extends('admin::layouts.auth')

@section('content')
    <p>
        <h2>{{ trans('admin.ext.2fa.title') }}</h2>
        <span>{!! trans('admin.ext.2fa.description_provide') !!}</span>
    </p>
    <form method="POST" action="" class="check-code">
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
