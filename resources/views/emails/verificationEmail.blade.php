@component('mail::message')
# Ram Points Team

Hello there here is your PIN code for verify your account


@component('mail::panel')
    {{ $token }}
@endcomponent

Thanks,<br>
{{ config('app.name') }} teams
@endcomponent
