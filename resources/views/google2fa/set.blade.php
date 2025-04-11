@extends('admin::layouts.auth')

@section('content')
    <p>
        <h2>Two-Factor Authentication</h2>
        <span>Set your authentication code in <b>Google Authenticator</b>. Scan the QR code and to confirm, enter the code in the box below to complete the login process.</span>
    </p>
    @if($qr_code_image)
        <img src="data:image/svg+xml;base64, {{ $qr_code_image}}" />
    @endif

    <div class="form-group">
        <label for="secret-key">Secret key</label>
        <input type="text" class="form-control" id="secret_key" name="secret_key" readonly value="{{ $secret_key  }}">
    </div>

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
