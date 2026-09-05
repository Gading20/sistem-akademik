<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menambahkan header keamanan standar ke setiap respons.
 *
 * - CSP membatasi sumber script/style/font/connect ke domain yang dipakai aplikasi
 *   (Tailwind CDN, Alpine CDN, Google Fonts) dan melarang iframe/object.
 * - X-Frame-Options: DENY mencegah clickjacking.
 * - X-Content-Type-Options: nosniff mencegah MIME sniffing.
 * - Referrer-Policy membatasi informasi referrer yang dikirim ke pihak lain.
 * - Permissions-Policy menonaktifkan fitur browser yang tidak dipakai (kamera, dll).
 * - HSTS hanya dikirim saat koneksi sudah HTTPS (produksi), agar tidak merusak
 *   pengembangan lokal via HTTP.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
                "font-src 'self' data: https://fonts.gstatic.com",
                "img-src 'self' data: blob:",
                "connect-src 'self'",
                "frame-ancestors 'none'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ])
        );

        if ($request->isSecure() || app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}