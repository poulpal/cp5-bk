<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogRedirect
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
        if(config('app.env') === 'local') {
            return $next($request);
        }
        // remove double slashes
        $path = preg_replace('/\/+/', '/', $request->path());
        if ($path !== $request->path()) {
            return redirect($path);
        }
        // if /blog/blog is in full path, redirect to /blog
        if (preg_match('/\/blog\/blog/', $request->fullUrl())) {
            return redirect(str_replace('/blog/blog', '/blog', $request->fullUrl()));
        }
        // return $next($request);
        $host = $request->getHost();
        if ($host !== 'chargepal.ir') {
            return redirect('https://chargepal.ir');
        }
        return $next($request);
    }
}
