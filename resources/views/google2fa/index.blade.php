@extends('admin::layouts.auth')

@section('content')
    <p>
        <h2>Two-Factor Authentication</h2>
        <span>Provide your authentication code from <b>Google Authenticator</b> in the field below to complete sign in.</span>
    </p>
    <form method="POST" action="">
        @csrf
        <div class="input-group" style="width: 100%">
            <input
                    type="text"
                    class="form-control {{ $errors->has('code') || $errors->has('google2fa_secret') ? 'is-invalid' : ''}} "
                    style="border-radius: 0 5px 5px 0"
                    placeholder="Enter code"
                    name="code"
                    minlength="6"
                    maxlength="6"
                    required>
            <button class="btn btn-default" type="submit" style="position: absolute; z-index: 100; right: 0;">Check code</button>
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
