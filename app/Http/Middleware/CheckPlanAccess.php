<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;

class CheckPlanAccess
{
    /**
     *     
     * 
     *   routes:
     * Route::get('/plans/{plan}', [...]) ->middleware('check.plan.access');
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission  - 'view', 'edit', 'manage'
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission = 'view')
    {
        //    route 
        $plan = $request->route('plan');

        if (!$plan instanceof Plan) {
            return response()->json([
                'message' => '  '
            ], 404);
        }

        $user = $request->user();

        //  
        if (!$this->checkPermission($plan, $user, $permission)) {
            return response()->json([
                'message' => '      '
            ], 403);
        }

        //  
        $this->logAccess($request, $plan, $permission);

        return $next($request);
    }

    /**
     *   
     */
    private function checkPermission(Plan $plan, $user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        return match ($permission) {
            'view' => $plan->canView($user),
            'edit' => $plan->canEdit($user),
            'manage' => $plan->canManageMembers($user),
            default => $plan->canView($user)
        };
    }

    /**
     *    
     */
    private function logAccess(Request $request, Plan $plan, string $permission): void
    {
        //    
        // ActivityLog::create([...])
    }
}
