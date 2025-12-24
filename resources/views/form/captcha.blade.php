<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} col-form-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div class="input-group" style="width: 250px;">

            <input {!! $attributes !!} />

            <div class="input-group-prepend clearfix" style="padding: 1px;"><span class="input-group-text"><img id="{{$column}}-captcha" src="{{ captcha_src() }}" style="height:30px;cursor: pointer;"  title="Click to refresh"/></span></div>

        </div>

        @include('admin::form.help-block')

    </div>
</div>
