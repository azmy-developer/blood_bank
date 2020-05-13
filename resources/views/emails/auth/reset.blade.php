@component('mail::message')
# Introduction

<p> Blood Bank Reset Password</p>

Hello {{$client->name}}

{{--@component('mail::button', ['url' => '','color'=>'success'])--}}
{{--Reset--}}
{{--@endcomponent--}}

<p>Your Reset Code Is : {{$client->rest_code_password}}</p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
