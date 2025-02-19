<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 0; margin: 0; width: 100%; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0; padding: 0; width: 100%; background-color: #f4f4f4;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0; padding: 0; width: 100%; max-width: 600px;">
                    <!-- Header -->
                    @if (isset($header))
                        {{ $header }}
                    @endif

                    <!-- Body -->
                    <tr>
                        <td style="padding: 25px; background-color: #ffffff;">
                            {{ $slot }}
                        </td>
                    </tr>

                    <!-- Subcopy -->
                    @if (isset($subcopy))
                        {{ $subcopy }}
                    @endif

                    <!-- Footer -->
                    @if (isset($footer))
                        {{ $footer }}
                    @endif
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
