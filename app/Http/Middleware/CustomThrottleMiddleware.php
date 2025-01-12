<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CustomThrottleMiddleware
{
    protected $limiter;

    public function __construct(RateLimiter $limiter){
        $this->limiter = $limiter;
    }

    public function handle($request, Closure $next, $maxAttempts = 60, $decaySeconds = 1){
        // $key = $request->ip(); // You can customize the key based on your needs
        $key = $request->ip().'_'.$request->path();
        // $key = $request->ip();

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response('Too Many Attempts.', Response::HTTP_TOO_MANY_REQUESTS);
        }

        // $this->limiter->hit($key, $decayMinutes * 60);
        $this->limiter->hit($key, $decaySeconds);

        $response = $next($request);

        return $response;
    }
}