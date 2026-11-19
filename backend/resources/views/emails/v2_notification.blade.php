<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'SI-APPIK Kabupaten' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #0F172A; -webkit-font-smoothing: antialiased;">
  @php
    $accentMap = [
      'primary' => '#2563EB',
      'success' => '#10B981',
      'warning' => '#F59E0B',
      'danger'  => '#DC2626',
    ];
    $accentColor = $accentMap[$tone ?? 'primary'] ?? '#2563EB';

    $badgeBgMap = [
      'primary' => '#EFF6FF',
      'success' => '#ECFDF5',
      'warning' => '#FFFBEB',
      'danger'  => '#FEF2F2',
    ];
    $badgeTextMap = [
      'primary' => '#1D4ED8',
      'success' => '#047857',
      'warning' => '#B45309',
      'danger'  => '#B91C1C',
    ];
    $badgeBg = $badgeBgMap[$tone ?? 'primary'] ?? '#EFF6FF';
    $badgeText = $badgeTextMap[$tone ?? 'primary'] ?? '#1D4ED8';
  @endphp
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #F8FAFC; padding: 28px 14px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 520px; background-color: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden;">
          <!-- Top Accent Line -->
          <tr>
            <td style="height: 4px; background-color: {{ $accentColor }}; font-size: 0; line-height: 0;">&nbsp;</td>
          </tr>

          <!-- Compact Brand Header -->
          <tr>
            <td style="padding: 18px 24px 14px 24px; border-bottom: 1px solid #F1F5F9;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                  <td style="vertical-align: middle;">
                    <span style="font-size: 15px; font-weight: 800; color: #0F172A; letter-spacing: -0.2px;">SI-APPIK</span>
                    <span style="font-size: 12px; font-weight: 500; color: #64748B;"> &bull; Kabupaten</span>
                  </td>
                  @if(!empty($badge))
                  <td align="right" style="vertical-align: middle;">
                    <span style="display: inline-block; background-color: {{ $badgeBg }}; color: {{ $badgeText }}; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px;">
                      {{ $badge }}
                    </span>
                  </td>
                  @endif
                </tr>
              </table>
            </td>
          </tr>

          <!-- Main Content -->
          <tr>
            <td style="padding: 24px;">
              <h1 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700; color: #0F172A; line-height: 1.35;">
                {{ $title }}
              </h1>

              @if(!empty($greeting))
              <p style="margin: 0 0 8px 0; font-size: 13.5px; color: #334155; line-height: 1.55;">
                {!! $greeting !!}
              </p>
              @endif

              <p style="margin: 0 0 18px 0; font-size: 13.5px; color: #475569; line-height: 1.6;">
                {!! $intro !!}
              </p>

              @if(!empty($otpCode))
              <!-- Clean OTP Block -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 4px 0 18px 0;">
                <tr>
                  <td align="center" style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 12px; padding: 20px 16px;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #1E40AF; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 8px;">
                      Kode Verifikasi
                    </div>
                    <div style="font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #1E3A8A; font-family: 'Courier New', Courier, monospace; line-height: 1.1;">
                      {{ $otpCode }}
                    </div>
                    @if(!empty($otpValidity))
                    <div style="margin-top: 10px; font-size: 12px; color: #1D4ED8; font-weight: 600;">
                      Berlaku {{ $otpValidity }}
                    </div>
                    @endif
                  </td>
                </tr>
              </table>
              @endif

              @if(!empty($details) && is_array($details))
              <!-- Clean Key-Value Summary -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; border-collapse: separate; margin: 4px 0 18px 0;">
                @foreach($details as $label => $value)
                <tr>
                  <td style="padding: 10px 14px; font-size: 12px; color: #64748B; font-weight: 600; width: 36%; vertical-align: top; {{ !$loop->last ? 'border-bottom: 1px solid #E2E8F0;' : '' }}">
                    {{ $label }}
                  </td>
                  <td style="padding: 10px 14px; font-size: 12.5px; color: #0F172A; font-weight: 600; vertical-align: top; {{ !$loop->last ? 'border-bottom: 1px solid #E2E8F0;' : '' }}">
                    {!! $value !!}
                  </td>
                </tr>
                @endforeach
              </table>
              @endif

              @if(!empty($actionUrl) && !empty($actionLabel))
              <!-- Primary CTA -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 4px 0 16px 0;">
                <tr>
                  <td align="center">
                    <a href="{{ $actionUrl }}" style="display: inline-block; background-color: #2563EB; color: #FFFFFF; font-size: 13px; font-weight: 700; text-decoration: none; padding: 11px 24px; border-radius: 10px;">
                      {{ $actionLabel }}
                    </a>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding-top: 8px; font-size: 11px; color: #94A3B8; word-break: break-all;">
                    Atau buka: <a href="{{ $actionUrl }}" style="color: #2563EB; text-decoration: none;">{{ $actionUrl }}</a>
                  </td>
                </tr>
              </table>
              @endif

              @if(!empty($note))
              @php
                $noteBgMap = [
                  'info'    => '#EFF6FF',
                  'warning' => '#FFFBEB',
                  'danger'  => '#FEF2F2',
                  'success' => '#ECFDF5',
                ];
                $noteBorderMap = [
                  'info'    => '#BFDBFE',
                  'warning' => '#FDE68A',
                  'danger'  => '#FECACA',
                  'success' => '#A7F3D0',
                ];
                $noteTextMap = [
                  'info'    => '#1E40AF',
                  'warning' => '#92400E',
                  'danger'  => '#991B1B',
                  'success' => '#065F46',
                ];
                $nt = $noteTone ?? 'info';
              @endphp
              <div style="background-color: {{ $noteBgMap[$nt] ?? '#EFF6FF' }}; border: 1px solid {{ $noteBorderMap[$nt] ?? '#BFDBFE' }}; border-radius: 10px; padding: 11px 14px; margin: 4px 0 0 0;">
                <p style="margin: 0; font-size: 12px; color: {{ $noteTextMap[$nt] ?? '#1E40AF' }}; line-height: 1.55;">
                  {!! $note !!}
                </p>
              </div>
              @endif
            </td>
          </tr>

          <!-- Minimal Footer -->
          <tr>
            <td style="background-color: #F8FAFC; border-top: 1px solid #F1F5F9; padding: 14px 24px; text-align: center;">
              <p style="margin: 0; font-size: 11px; color: #94A3B8; line-height: 1.5;">
                Dinas PUPR Kabupaten &bull; Email otomatis, mohon tidak membalas.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
