<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Illuminate\Support\Facades\Log;

class Authorization
{
    // Check Role_ID
    // public function handle($request, Closure $next, ...$roles)
    // {

    //     if (auth()->check()) {
    //         $role_id = auth()->user()->role_id;

    //         if (in_array($role_id, $roles)) {
    //             return $next($request);
    //         }
    //     }

    //     return response()->json(["error" => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    // }

    public function handle($request, Closure $next, ...$roles)
    {
        // Log::info('RoleMiddleware handle method called');
        try {
            $user = FacadesJWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }
            $userRole = $user->role->name;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!in_array($userRole, $roles)) {
            return response()->json(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}