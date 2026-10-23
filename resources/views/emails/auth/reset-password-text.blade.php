AlBabor

{{ __('passwords.email.title') }}

{{ __('passwords.email.greeting') }}

{{ __('passwords.email.intro') }}

{{ __('passwords.email.button') }}:
{!! $actionUrl !!}

{{ __('passwords.email.expiry', ['minutes' => $expiryMinutes]) }}

{{ __('passwords.email.notice_title') }}
{{ __('passwords.email.notice_body') }}

{{ __('passwords.email.signoff') }}
{{ __('passwords.email.team') }}

{{ $siteUrl }}
{{ __('passwords.email.privacy') }}: {{ $siteUrl }}/politique-confidentialite
