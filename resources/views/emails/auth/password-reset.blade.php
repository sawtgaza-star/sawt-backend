{{-- Auth: password reset OTP --}}
@php
    /** @var string $name */
    /** @var string $code */
    /** @var int $minutes */
@endphp

@component('emails.layout', [
    'title' => 'رمز إعادة تعيين كلمة المرور',
    'heading' => 'مرحباً '.($name ?: 'عزيزي المستخدم'),
    'subheading' => 'استخدم الرمز التالي لإعادة تعيين كلمة المرور',
    'headerColor' => '#0F3D2E',
    'headerIcon' => '🔑',
])
    <p style="margin:0 0 16px;">رمز التحقق لإعادة تعيين كلمة المرور هو:</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        <tr>
            <td align="center" style="background:#f3f4f6;border-radius:12px;padding:20px 16px;">
                <div style="color:#0F3D2E;font-size:32px;font-weight:700;letter-spacing:6px;font-family:Consolas,Monaco,monospace;direction:ltr;">
                    {{ $code }}
                </div>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;">ينتهي الرمز خلال <strong>{{ $minutes }}</strong> دقيقة.</p>
    <p style="margin:0;color:#6b7280;font-size:13px;">إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة.</p>
@endcomponent
