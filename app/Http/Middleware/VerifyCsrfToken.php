<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Cookie;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
	 * Add the CSRF token to the response cookies.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Http\Response  $response
	 * @return \Illuminate\Http\Response
	 */
	protected function addCookieToResponse($request, $response)
	{
	    $config = config('session');
	    $response->headers->setCookie(
	        new Cookie(
	            'XSRF-TOKEN',
	            $request->session()->token(),
	            $this->availableAt(60 * $config['lifetime']),
	            $config['path'],
	            $config['domain'],
	            $config['secure'],
	            $config['http_only'],
	            false,
	            $config['same_site'] ?? null
	        )
	    );

	    return $response;
	}

}
