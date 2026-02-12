<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OfficerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has officer role
        if (!$user->hasRole('officer')) {
            abort(403, 'Access denied. Officer role required.');
        }

        // Check if user has any active assignments
        $hasActiveAssignment = \App\Models\TenantOfficer::where('officer_id', $user->id)
            ->where('status', true)
            ->exists();

        if (!$hasActiveAssignment) {
            abort(403, 'Access denied. No active duka assignments found.');
        }

        return $next($request);
    }
}
