<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (auth()->check()) {
            $user = auth()->user();
            if ($user?->role?->permissions->count() > 0 || $user->role->name === "Admin") {
                return $next($request);
            } else {
                Auth::guard('web')->logout();
                return redirect('/');
            }
        } else {
            return redirect('/');
        }
        return $next($request);
    }
}
