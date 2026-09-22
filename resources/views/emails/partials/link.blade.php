{{-- Secondary text link under primary CTA --}}
@php
    $url = $url ?? '#';
    $label = $label ?? '';
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <a href="{{ $url }}" style="color:#0F3D2E;font-size:13px;text-decoration:underline;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
