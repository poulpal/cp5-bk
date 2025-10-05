<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('X-Accept-Language');
        if ($locale) {
            app()->setLocale($locale);
            $request->headers->set('Accept-Language', $locale);
        }
        return $next($request);
    }
}
