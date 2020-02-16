<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdminStatus
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
        if (!(User::pluck('id')->isAdmin())) 
        {
            abort(403, "You do not have the credentials to perform this action."); // forbidden
        }
        return $next($request);
    }
}
