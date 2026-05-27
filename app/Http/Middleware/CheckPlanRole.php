<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;

class CheckPlanRole
{
    /**
     * Проверить роль пользователя в плане
     * 
     * Использование:
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
                'message' => 'План не найден'
            ], 404);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Не авторизован'
            ], 401);
        }

        // Получить роль пользователя в плане
        $userRole = $plan->getUserRole($user);

        // Проверить, есть ли у пользователя одна из требуемых ролей
        if (!$this->hasRole($userRole, $roles)) {
            return response()->json([
                'message' => 'У вас недостаточно прав для этого действия',
                'required_roles' => $roles,
                'your_role' => $userRole
            ], 403);
        }

        return $next($request);
    }

    /**
     * Проверить, есть ли у пользователя требуемая роль
     */
    private function hasRole(?string $userRole, array $allowedRoles): bool
    {
        if (!$userRole) {
            return false;
        }

        // Иерархия ролей: admin > editor > viewer
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
