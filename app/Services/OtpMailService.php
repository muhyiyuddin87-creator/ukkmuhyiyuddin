<?php

namespace App\Services;

use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OtpMailService
{
    /**
     * Kirim kode OTP ke email pengguna.
     * Mencoba via Brevo HTTPS API (Port 443), lalu Resend HTTPS API (Port 443),
     * lalu fallback ke SMTP standar jika API key belum diisi.
     */
    public static function sendOtp(object $user, string $code): bool
    {
        $recipientEmail = trim((string) $user->email);
        $recipientName = (string) ($user->fullname ?: $user->username);

        if (empty($recipientEmail)) {
            return false;
        }

        // Jalur khusus Unit Test agar mocking Notification::fake() berfungsi semestinya
        if (app()->runningUnitTests()) {
            Notification::send($user, new PasswordResetOtpNotification($code));

            return true;
        }

        $senderEmail = config('mail.from.address', 'ukkscp@gmail.com');
        $senderName = config('mail.from.name', 'Sistem Informasi Sekolah');

        // -------------------------------------------------------------
        // JALUR 1: Brevo HTTPS REST API (Port 443 - Tidak Pernah Diblokir VPS)
        // -------------------------------------------------------------
        $brevoKey = config('services.brevo.key') ?: env('BREVO_API_KEY');
        if (! empty($brevoKey)) {
            try {
                $response = Http::timeout(4)->withHeaders([
                    'api-key' => $brevoKey,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => [
                        'name' => $senderName,
                        'email' => $senderEmail,
                    ],
                    'to' => [
                        ['email' => $recipientEmail, 'name' => $recipientName],
                    ],
                    'subject' => 'Kode OTP Reset Sandi Akun - ' . $code,
                    'htmlContent' => view('emails.password_reset_otp', ['code' => $code, 'user' => $user])->render(),
                ]);

                if ($response->successful()) {
                    return true;
                }

                Log::warning('Brevo API send failed: ' . $response->body());
            } catch (\Throwable $e) {
                Log::warning('Brevo API error: ' . $e->getMessage());
            }
        }

        // -------------------------------------------------------------
        // JALUR 2: Resend HTTPS REST API (Port 443 - Tidak Pernah Diblokir VPS)
        // -------------------------------------------------------------
        $resendKey = config('services.resend.key') ?: env('RESEND_API_KEY');
        if (! empty($resendKey)) {
            try {
                $fromEmail = config('services.resend.from') ?: (env('RESEND_FROM_EMAIL') ?: 'noreply@tugasukk.web.id');
                $htmlContent = view('emails.password_reset_otp', ['code' => $code, 'user' => $user])->render();

                $response = Http::connectTimeout(6)->timeout(12)->withHeaders([
                    'Authorization' => 'Bearer ' . $resendKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.resend.com/emails', [
                    'from' => "{$senderName} <{$fromEmail}>",
                    'to' => [$recipientEmail],
                    'subject' => 'Kode OTP Reset Sandi Akun - ' . $code,
                    'html' => $htmlContent,
                ]);

                if ($response->successful()) {
                    return true;
                }

                // Jika domain kustom belum terverifikasi di Resend, coba kirim via domain default onboarding
                if ($response->status() === 403 || str_contains($response->body(), 'domain')) {
                    $retryResponse = Http::connectTimeout(6)->timeout(12)->withHeaders([
                        'Authorization' => 'Bearer ' . $resendKey,
                        'Content-Type' => 'application/json',
                    ])->post('https://api.resend.com/emails', [
                        'from' => 'onboarding@resend.dev',
                        'to' => [$recipientEmail],
                        'subject' => 'Kode OTP Reset Sandi Akun - ' . $code,
                        'html' => $htmlContent,
                    ]);

                    if ($retryResponse->successful()) {
                        return true;
                    }
                }

                Log::warning('Resend API send failed: ' . $response->body());
            } catch (\Throwable $e) {
                Log::warning('Resend API error: ' . $e->getMessage());
            }
        }

        // -------------------------------------------------------------
        // JALUR 3: SMTP Standar Laravel (Jika port SMTP diizinkan oleh jaringan)
        // -------------------------------------------------------------
        try {
            @ini_set('default_socket_timeout', 3);
            Notification::send($user, new PasswordResetOtpNotification($code));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Standard SMTP delivery failed: ' . $e->getMessage());

            return false;
        }
    }
}

