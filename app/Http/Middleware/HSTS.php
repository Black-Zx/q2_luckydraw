<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HSTS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubdomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; style-src 'self' 'sha256-ixVUGs3ai0rMA0pgIVBN0KVlYbQip7/5SGmnUwJPNqE=' 'nonce-5157' fonts.googleapis.com cdnjs.cloudflare.com; font-src 'self' fonts.gstatic.com; script-src 'self' 'sha256-2TRacEOpo1XST8L+YCHXD77gbM0V59aJCVqQTWS13mw=' 'nonce-5157' https://www.googletagmanager.com https://analytics.google.com cdnjs.cloudflare.com; connect-src 'self' https://analytics.google.com https://stats.g.doubleclick.net https://www.google-analytics.com; img-src 'self' blob: data: https://analytics.google.com https://www.google.com.my");

        return $response;
    }
}
