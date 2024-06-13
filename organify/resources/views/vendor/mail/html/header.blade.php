@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Organify')
<img src="{{ asset('ORGANIFY_LOGO.png') }}" class="logo" alt="Organify Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
