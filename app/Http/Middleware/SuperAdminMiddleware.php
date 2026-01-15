<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login first.');
        }

        $user = Auth::user();

        // Check if user has super-admin role
        if (!$user->hasRole('superadmin')) {
            return abort(403, 'Unauthorized: Super Admin access only.');
        }

        return $next($request);
    }
}
