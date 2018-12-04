<?php

namespace Melatop\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Melatop\User;
use Exception;
class LastOnline
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
        try {
            $user=Auth::user();
            $user->update(['last_online'=>Carbon::now()]);
        }
        catch (Exception $e) {
            return $next($request);
        }
        
        return $next($request);
        
    }
}
