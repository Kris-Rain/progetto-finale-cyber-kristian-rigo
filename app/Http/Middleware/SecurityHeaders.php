<?php
// filepath: /Users/krisrain/Library/Mobile Documents/com~apple~CloudDocs/Cybersecurity/Aulab/final-project-cyber-blog/app/Http/Middleware/SecurityHeaders.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Blocca clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Blocca MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Rimuove X-Powered-By (info leakage)
        $response->headers->remove('X-Powered-By');

        // Content Security Policy base
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "base-uri 'self'; " .
            "object-src 'none'; " .
            "frame-ancestors 'self'; " .
            "form-action 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tiny.cloud; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tiny.cloud; " .
            "img-src 'self' data: https://cdn.tiny.cloud https://sp.tinymce.com; " .
            "font-src 'self' https://cdn.jsdelivr.net https://cdn.tiny.cloud; " .
            "frame-src 'self'; " .
            "connect-src 'self' https://cdn.tiny.cloud https://sp.tinymce.com"
        );

        return $response;
    }
}