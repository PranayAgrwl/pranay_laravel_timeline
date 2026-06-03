{{--
    Email template: login notification.
    Inline CSS only (most email clients strip <style> blocks). Stays simple
    so it renders cleanly in Gmail, Outlook, Apple Mail, mobile, etc.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New login</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family:Arial, Helvetica, sans-serif; color:#212529;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
           width="100%" style="background-color:#f4f5f7; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                       width="560" style="max-width:560px; background-color:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e6e8eb;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#212529; color:#ffffff; padding:18px 24px; font-size:18px; font-weight:bold;">
                            {{ config('app.name') }}
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:24px;">
                            <h2 style="margin:0 0 12px; font-size:18px; color:#212529;">
                                Hi {{ $user->name }},
                            </h2>

                            <p style="margin:0 0 16px; font-size:14px; line-height:1.55;">
                                Your account was just signed in to
                                @if ($viaRememberCookie)
                                    using a saved "Remember Me" cookie on a previously trusted device.
                                @else
                                    using your email and password.
                                @endif
                            </p>

                            {{-- Details table --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                   width="100%" style="background-color:#f8f9fa; border:1px solid #e6e8eb; border-radius:6px; margin:0 0 20px;">
                                <tr>
                                    <td style="padding:10px 14px; font-size:13px; width:120px; color:#6c757d;">When</td>
                                    <td style="padding:10px 14px; font-size:13px;">{{ $loginAt->format('l, F jS Y \a\t g:i A') }} ({{ $loginAt->timezoneName }})</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px; font-size:13px; color:#6c757d; border-top:1px solid #e6e8eb;">IP address</td>
                                    <td style="padding:10px 14px; font-size:13px; border-top:1px solid #e6e8eb;">{{ $ipAddress }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px; font-size:13px; color:#6c757d; border-top:1px solid #e6e8eb;">Browser</td>
                                    <td style="padding:10px 14px; font-size:13px; border-top:1px solid #e6e8eb;">{{ $browser }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px; font-size:13px; color:#6c757d; border-top:1px solid #e6e8eb;">Operating system</td>
                                    <td style="padding:10px 14px; font-size:13px; border-top:1px solid #e6e8eb;">{{ $os }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px; font-size:11px; color:#adb5bd; border-top:1px solid #e6e8eb;" colspan="2">
                                        Raw user-agent:<br>
                                        <span style="word-break:break-all;">{{ $userAgentRaw }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:14px; line-height:1.55;">
                                If this was you, no action is needed.
                            </p>

                            <p style="margin:0 0 12px; font-size:14px; line-height:1.55; color:#b02a37;">
                                <strong>If this wasn't you,</strong> please change your password immediately
                                from the Edit Profile page and consider revoking any active "Remember Me" sessions.
                            </p>

                            <p style="margin:18px 0 0; font-size:12px; color:#6c757d;">
                                Sent automatically by {{ config('app.name') }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
