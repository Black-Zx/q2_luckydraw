<?php

// Localization.php

namespace App\Http\Middleware;

use Closure;
use App;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        } else {
            // locale session not exist, initialize as english
            session()->put('locale', 'en');
            App::setLocale(session()->get('locale'));
        };
        return $next($request);
    }
}