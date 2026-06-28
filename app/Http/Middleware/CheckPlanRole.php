<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;

class CheckPlanRole
{
    /**
     *     
     * 
     * :
     * Route::post('/plans/{plan}/members', [...]) ->middleware('check.plan.role:admin');
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  - 'admin', 'editor', 'viewer'
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $plan = $request->route('plan');

        if (!$plan instanceof Plan) {
            return response()->json([
                'message' => '  '
            ], 404);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => ' '
            ], 401);
        }

        //     
        $userRole = $plan->getUserRole($user);

        // ,        
        if (!$this->hasRole($userRole, $roles)) {
            return response()->json([
                'message' => '      ',
                'required_roles' => $roles,
                'your_role' => $userRole
            ], 403);
        }

        return $next($request);
    }

    /**
     * ,      
     */
    private function hasRole(?string $userRole, array $allowedRoles): bool
    {
        if (!$userRole) {
            return false;
        }

        //  : admin > editor > viewer
        $roleHierarchy = [
            'admin' => 3,
            'editor' => 2,
            'viewer' => 1
        ];

        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $maxRequired = max(array_map(
            fn($role) => $roleHierarchy[$role] ?? 0,
            $allowedRoles
        ));

        return $userLevel >= $maxRequired;
    }
}
