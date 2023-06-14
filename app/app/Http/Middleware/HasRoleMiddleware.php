<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasRoleMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $role) {

        $authGuard = auth();

        // if ($authGuard->guest()) {
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ], 401);
        // }
        if(!$user = $authGuard()->user){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if($role == 'all'){
            return $next($request);
        }

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }


        return $next($request);

    }
}
