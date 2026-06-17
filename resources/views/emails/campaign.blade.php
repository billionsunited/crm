@php
    $theme = $theme ?? 'default';
    
    // Default/Navy Theme
    $headerBg = '#0f172a';
    $footerBgFallback = '#000000';
    $footerGradient = 'linear-gradient(15deg, #000000 0%, #000000 35%, #f97316 35%, #f97316 45%, #ef4444 45%, #ef4444 100%)';
    
    if ($theme === 'emerald') {
        $headerBg = '#064e3b';
        $footerBgFallback = '#022c22';
        $footerGradient = 'linear-gradient(15deg, #022c22 0%, #022c22 35%, #d97706 35%, #d97706 45%, #10b981 45%, #10b981 100%)';
    } elseif ($theme === 'purple') {
        $headerBg = '#1e1b4b';
        $footerBgFallback = '#2e1065';
        $footerGradient = 'linear-gradient(15deg, #2e1065 0%, #2e1065 35%, #db2777 35%, #db2777 45%, #8b5cf6 45%, #8b5cf6 100%)';
    } elseif ($theme === 'charcoal') {
        $headerBg = '#111827';
        $footerBgFallback = '#030712';
        $footerGradient = 'linear-gradient(15deg, #030712 0%, #030712 35%, #f59e0b 35%, #f59e0b 45%, #6b7280 45%, #6b7280 100%)';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <!-- PREHEADER TEXT (Invisible in email body, but displays in inbox snippets) -->
    <div style="display: none; max-height: 0px; overflow: hidden; font-size: 0px; color: transparent; line-height: 0px; mso-hide: all;">
        {{ trim(preg_replace('/\s+/', ' ', strip_tags($body))) }}
    </div>
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" max-width="600" border="0" cellspacing="0" cellpadding="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); border: 1px solid #e5e7eb;">
                    <!-- HEADER SECTION -->
                    <tr>
                        <td style="background-color: {{ $headerBg }}; padding: 24px 40px; text-align: center;">
                            <div style="font-size: 26px; font-weight: 800; color: #ffffff; font-family: 'Inter', sans-serif; letter-spacing: 0.5px; text-transform: uppercase;">
                                {{ in_array($theme, ['purple', 'charcoal']) ? 'UPDATES' : 'Billions United' }}
                            </div>
                        </td>
                    </tr>
                    
                    <!-- EMAIL CONTENT AREA -->
                    <tr>
                        <td style="padding: 32px 40px 40px 40px; background-color: #ffffff; color: #334155; font-size: 16px; line-height: 1.6; font-family: 'Inter', sans-serif; text-align: left;">

                            <!-- Template Content (Injecting the HTML template body) -->
                            <div style="color: #334155; font-size: 15px; line-height: 1.7; font-family: 'Inter', sans-serif;">
                                {!! nl2br($body) !!}
                            </div>
                        </td>
                    </tr>
                    
                    <!-- GEOMETRIC ABSTRACT FOOTER DECORATION -->
                    <tr>
                        <td height="40" style="height: 40px; background-color: {{ $footerBgFallback }}; background: {{ $footerGradient }};">
                            &nbsp;
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
