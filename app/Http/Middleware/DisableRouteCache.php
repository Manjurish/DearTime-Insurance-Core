<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableRouteCache
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
        return $next($request)->withHeaders([
            "Cache-Control" => "must-revalidate, no-store, max-age=0, private",
        ]);
    }
}
