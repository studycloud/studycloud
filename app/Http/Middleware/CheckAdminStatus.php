<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

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
        //return $next($request);

        if (Auth::user() &&  Auth::user()->isAdmin() == 0) //user is NOT an admin
        {
            abort(403, "You do not have the credentials to perform this action."); // forbidden
        }
        return $next($request);
    }
}
