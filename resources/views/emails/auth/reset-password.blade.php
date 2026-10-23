<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ __('passwords.email.subject') }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0; mso-table-rspace: 0; }
        img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        @media only screen and (max-width: 600px) {
            .email-shell { padding: 12px 12px 24px !important; }
            .email-brand { padding: 20px 16px 26px !important; }
            .email-content { padding: 30px 24px 28px !important; }
            .email-title { font-size: 27px !important; line-height: 35px !important; }
            .email-fallback { padding: 22px 24px !important; }
            .email-button { font-size: 15px !important; padding: 17px 12px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f0f4f8; font-family: Arial, Helvetica, sans-serif; color: #3e5265;">
    <div style="display: none; font-size: 1px; line-height: 1px; color: #f0f4f8; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all;">
        {{ __('passwords.email.preheader', ['minutes' => $expiryMinutes]) }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f0f4f8">
        <tr>
            <td class="email-shell" align="center" style="padding: 28px 16px 36px;">
                <!--[if mso]><table role="presentation" width="600" align="center"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                    <tr>
                        <td class="email-brand" align="center" style="padding: 18px 24px 30px;">
                            <a href="{{ $siteUrl }}" style="text-decoration: none;">
                                <img src="{{ $logoUrl }}" alt="AlBabor" width="228" height="32" style="display: block; width: 228px; height: auto; max-width: 100%; margin: 0 auto; color: #081d43; font-size: 28px; font-weight: bold;">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #ffffff; border: 1px solid #e0e7ee; border-radius: 18px; overflow: hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout: fixed;">
                                <tr>
                                    <td height="5" bgcolor="#cba64f" style="height: 5px; line-height: 5px; font-size: 1px; border-radius: 18px 18px 0 0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="email-content" style="padding: 38px 40px 34px;">
                                        <p style="margin: 0 0 16px; color: #9b7529; font-size: 10px; line-height: 16px; font-weight: bold; letter-spacing: 2px;">
                                            {{ __('passwords.email.eyebrow') }}
                                        </p>
                                        <h1 class="email-title" style="margin: 0 0 26px; color: #081d43; font-size: 32px; line-height: 40px; font-weight: bold; letter-spacing: -0.7px;">
                                            {{ __('passwords.email.title') }}
                                        </h1>
                                        <p style="margin: 0 0 12px; font-size: 16px; line-height: 26px; color: #263e56;">
                                            {{ __('passwords.email.greeting') }}
                                        </p>
                                        <p style="margin: 0 0 28px; font-size: 15px; line-height: 25px; color: #526477;">
                                            {{ __('passwords.email.intro') }}
                                        </p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" bgcolor="#0c2851" style="border-radius: 10px; mso-padding-alt: 18px 24px;">
                                                    <a class="email-button" href="{{ $actionUrl }}" style="display: block; padding: 18px 24px; border: 1px solid #0c2851; border-radius: 10px; color: #ffffff; font-size: 16px; line-height: 22px; font-weight: bold; text-align: center; text-decoration: none;">
                                                        {{ __('passwords.email.button') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 13px 8px 28px; color: #758495; font-size: 12px; line-height: 19px; text-align: center;">
                                            {{ __('passwords.email.expiry', ['minutes' => $expiryMinutes]) }}
                                        </p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td bgcolor="#f4f7fa" style="padding: 17px 18px; border-radius: 10px; border-left: 3px solid #d7e1eb;">
                                                    <p style="margin: 0 0 5px; color: #304b65; font-size: 13px; line-height: 20px; font-weight: bold;">{{ __('passwords.email.notice_title') }}</p>
                                                    <p style="margin: 0; color: #6b7d8f; font-size: 12px; line-height: 20px;">{{ __('passwords.email.notice_body') }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin: 25px 0 0; font-size: 13px; line-height: 22px; color: #6b7d8f;">
                                            {{ __('passwords.email.signoff') }}<br>
                                            <strong style="color: #263e56;">{{ __('passwords.email.team') }}</strong>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="email-fallback" bgcolor="#fbfcfd" style="padding: 22px 40px 25px; border-top: 1px solid #eaf0f5; border-radius: 0 0 18px 18px;">
                                        <p style="margin: 0 0 4px; color: #526477; font-size: 12px; line-height: 19px; font-weight: bold;">{{ __('passwords.email.fallback_title') }}</p>
                                        <p style="margin: 0 0 9px; color: #7a8997; font-size: 11px; line-height: 18px;">{{ __('passwords.email.fallback_body') }}</p>
                                        <p style="margin: 0; font-size: 11px; line-height: 18px; word-break: break-all; overflow-wrap: anywhere;">
                                            <a href="{{ $actionUrl }}" style="color: #496780; text-decoration: underline; word-break: break-all; overflow-wrap: anywhere;">{{ $actionUrl }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 25px 20px 0;">
                            <p style="margin: 0 0 10px; color: #7c8d9e; font-size: 12px; line-height: 19px;">{{ __('passwords.email.tagline') }}</p>
                            <p style="margin: 0 0 10px; font-size: 11px; line-height: 18px;">
                                <a href="{{ $siteUrl }}" style="color: #536b82; text-decoration: none;">albabor.com</a>
                                <span style="padding: 0 9px; color: #bdc7d1;">&middot;</span>
                                <a href="{{ $siteUrl }}/politique-confidentialite" style="color: #536b82; text-decoration: none;">{{ __('passwords.email.privacy') }}</a>
                            </p>
                            <p style="margin: 0; color: #91a0ad; font-size: 10px; line-height: 17px;">&copy; {{ date('Y') }} AlBabor. {{ __('passwords.email.rights') }}</p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
