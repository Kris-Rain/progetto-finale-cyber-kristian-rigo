<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitExceededHandler
{
    protected int $blockDurationMinutes = 10;  // Durata del blocco in minuti

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $blockedKey = 'blocked_ip:' . sha1($ip);

        // Controlla se l'IP è già bloccato e restituisce un messaggio di errore
        if (Cache::has($blockedKey)) {
            Log::critical(
                'Blocked IP: ' . $ip
                . ' attempted access on path: ' . $request->path()
            );
            $message = "Your IP has been blocked for {$this->blockDurationMinutes} minute(s) due to too many requests.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 429);
            }
            abort(429, $message);
        }

        $response = $next($request);

        // Se riceve 429, blocca subito l'IP
        if ($response->getStatusCode() === 429) {
            Cache::put($blockedKey, true, now()->addMinutes($this->blockDurationMinutes));

            Log::critical(
                'Rate limit exceeded - IP: ' . $ip
                . ' blocked for ' . $this->blockDurationMinutes
                . ' minute(s) - Path: ' . $request->path()
                . ' - Method: ' . $request->method()
            );
        }
        return $response;
    }
}
