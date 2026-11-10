<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * =========================================================================
 * FITUR TC-SEC-05: MIDDLEWARE PREVENT BACK HISTORY (HEADER ANTI-BFCACHE)
 * =========================================================================
 * Mengapa middleware ini dibutuhkan?
 * Ketika pengguna mengklik 'Logout', sesi di server memang telah dihapus.
 * Namun, browser modern memiliki fitur bfcache (Back-Forward Cache) yang menyimpan
 * screenshot/DOM memori halaman terakhir. Jika tombol 'Back' ditekan, browser
 * bisa saja menampilkan halaman internal dari cache lokal tanpa meminta ke server.
 * 
 * Solusi:
 * Middleware ini menyematkan header HTTP:
 * - Cache-Control: no-cache, no-store, max-age=0, must-revalidate
 * - Pragma: no-cache
 * - Expires: Sun, 02 Jan 1990 00:00:00 GMT
 * 
 * Dengan header ini, browser dipaksa menghubungi server setiap kali tombol Back
 * ditekan. Karena sesi sudah hangus, server otomatis mengalihkan pengguna ke login.
 */
class PreventBackHistory
{
    /**
     * Menangani request yang masuk dan menyematkan header anti-cache pada response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Header HTTP untuk mencegah browser menyimpan cache halaman internal
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
