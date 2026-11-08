<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Sandi</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow: hidden; border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0284c7 0%, #4f46e5 100%); padding: 32px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px;">
                                Sistem Informasi Sekolah
                            </h1>
                            <p style="margin: 6px 0 0; color: #e0e7ff; font-size: 13px;">
                                Permintaan Atur Ulang Sandi Akun
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 30px; color: #334155;">
                            <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.6;">
                                Halo, <strong>{{ $user->fullname ?: $user->username }}</strong>
                            </p>
                            <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.6; color: #64748b;">
                                Kami menerima permintaan untuk mengatur ulang sandi akun Anda. Gunakan kode verifikasi (OTP) 6-digit di bawah ini:
                            </p>

                            <!-- Kode OTP Box -->
                            <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px; padding: 20px; text-align: center; margin: 0 0 24px;">
                                <span style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #1e293b;">
                                    {{ $code }}
                                </span>
                                <p style="margin: 8px 0 0; font-size: 12px; color: #94a3b8;">
                                    Berlaku selama 10 menit
                                </p>
                            </div>

                            <p style="margin: 0 0 16px; font-size: 13px; line-height: 1.6; color: #64748b;">
                                Jangan bagikan kode ini kepada siapa pun, termasuk pihak sekolah atau administrator.
                            </p>
                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #94a3b8;">
                                Jika Anda tidak merasa meminta pengaturan ulang sandi ini, silakan abaikan email ini. Sandi akun Anda tetap aman.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #f1f5f9; padding: 20px 30px; text-align: center; color: #94a3b8; font-size: 11px;">
                            © {{ date('Y') }} Sistem Informasi Sekolah & UKK. Dikirim secara otomatis via HTTPS API.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

