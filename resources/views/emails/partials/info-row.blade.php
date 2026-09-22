{{-- Gray info card row used inside email bodies --}}
@php
    $label = $label ?? '';
    $value = $value ?? '';
    $danger = (bool) ($danger ?? false);
@endphp
<tr>
    <td style="background:{{ $danger ? '#FEF2F2' : '#f3f4f6' }};border-radius:12px;padding:14px 16px;{{ $danger ? 'border:1px solid #FECACA;' : '' }}">
        <div style="color:{{ $danger ? '#991B1B' : '#6b7280' }};font-size:12px;margin-bottom:4px;">{{ $label }}</div>
        <div style="color:{{ $danger ? '#7F1D1D' : '#0F3D2E' }};font-size:{{ $danger ? '14px' : '16px' }};font-weight:{{ $danger ? '400' : '700' }};">{{ $value }}</div>
    </td>
</tr>
