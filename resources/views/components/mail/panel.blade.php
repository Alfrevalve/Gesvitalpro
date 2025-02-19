<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-content">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-item">
{{ $slot }}
</td>
</tr>
</table>
</td>
</tr>
</table>

<style>
.panel {
    margin: 25px 0;
}
.panel-content {
    background-color: #f5f5f5;
    padding: 16px;
    border-radius: 4px;
}
.panel-item {
    padding: 0;
    color: #444444;
}
</style>
