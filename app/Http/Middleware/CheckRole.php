<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            Log::warning('Unauthenticated access attempt', [
                'ip' => $request->ip(),
                'route' => $request->path(),
                'method' => $request->method(),
                'required_role' => $role
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Handle multiple roles passed as comma-separated values
        $roles = explode(',', $role);
        if (!in_array($request->user()->role, $roles)) {
            Log::warning('Unauthorized access attempt - Insufficient role', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'user_role' => $request->user()->role,
                'required_roles' => $roles,
                'route' => $request->path(),
                'method' => $request->method()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access this resource.'
            ], 403);
        }

        // Log successful role check for auditing purposes
        Log::info('Role check passed', [
            'user_id' => $request->user()->id,
            'role' => $request->user()->role,
            'route' => $request->path(),
            'method' => $request->method()
        ]);

        return $next($request);
    }
} 