@props(['url'])
<tr>
    <td style="padding: 25px; background-color: #ffffff;">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td style="text-align: center;">
                    <a href="{{ $url }}" style="display: inline-block;">
                        <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" style="max-width: 200px; height: auto;">
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td style="height: 2px; background-color: #3869d4;"></td>
</tr>
