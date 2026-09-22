{{-- Primary CTA button for transactional emails --}}
@php
    $url = $url ?? '#';
    $label = $label ?? 'متابعة';
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding-bottom:10px;">
            <a href="{{ $url }}"
               style="display:inline-block;background:#0F3D2E;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:999px;font-size:15px;font-weight:700;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
