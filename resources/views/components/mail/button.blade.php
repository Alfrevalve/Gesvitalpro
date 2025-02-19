<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener">{{ $slot }}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>

<style>
.button {
    display: inline-block;
    padding: 8px 20px;
    background-color: #3869d4;
    border-radius: 4px;
    color: #ffffff;
    font-size: 15px;
    line-height: 24px;
    text-align: center;
    text-decoration: none;
    -webkit-text-size-adjust: none;
}

.button-primary {
    background-color: #3869d4;
}

.button-success {
    background-color: #48bb78;
}

.button-warning {
    background-color: #ecc94b;
}

.button-danger {
    background-color: #e53e3e;
}
</style>
