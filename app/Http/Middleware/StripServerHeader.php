<?php

namespace App\Http\Middleware;

use Closure;

class StripServerHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    // Enumerate unwanted headers
    private $unwantedHeaderList = [
        'Server',
    ];

    public function handle($request, Closure $next)
    {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);

        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    /**
     * @param $headerList
     */
    private function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header){
            header_remove($header);
        }
    }
}
